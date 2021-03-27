#!/usr/bin/python3

# HTTP Injector for Python3
# modded by Lutfa Ilham
# v1.0
# credit: https://github.com/mientz/python-http-injector

import socket
import select
import time
import argparse
import json
import logging
import sys

logging.basicConfig(filename='/tmp/http-injector.log',
                    filemode='w',
                    format='%(asctime)s %(message)s',
                    level=logging.DEBUG)


# forward trafic to remote proxy
class Forwarder:
    def __init__(self):
        self.forward = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

    def start(self, host, port):
        try:
            self.forward.connect((host, port))
            return self.forward
        except Exception as e:
            print(e)
            logging.error(e)
            return False


# injector server
class Server:
    sockets = []
    tx_chan = {}
    rx_chan = {}
    request = {}

    def __init__(self, config, port):
        # load payload config
        payload_file = json.load(config)
        payload = payload_file['http']['payload']
        payload = payload.replace('[crlf]', '\r\n')
        payload = payload.replace('[lf]', '\n')
        payload = payload.replace('[cr]', '\r')
        payload = payload.replace('[protocol]', 'HTTP/1.1')

        # initalize payload
        self.payload = payload
        self.forward_to = (payload_file['http']['proxy']['ip'],
                           payload_file['http']['proxy']['port'])
        self.buffer_size = payload_file['http']['buffer']

        # initalize injector server
        self.server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        self.server.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
        self.server.bind(('127.0.0.1', port))
        self.server.listen(200)

        print('Config: \033[96m{}\033[0m'.format(payload_file['http']['info']))
        logging.info('Config: \033[96m{}\033[0m'.format(payload_file['http']['info']))

    def on_accept(self):
        forward = Forwarder().start(self.forward_to[0], self.forward_to[1])
        clientsock, _clientaddr = self.server.accept()
        if forward:
            self.sockets.append(clientsock)
            self.sockets.append(forward)
            self.tx_chan[clientsock] = forward
            self.tx_chan[forward] = clientsock
            self.rx_chan[clientsock] = forward
            self.rx_chan[forward] = forward
        else:
            print('\033[91mUnable connect to proxy server\033[0m')
            logging.error('\033[91mUnable connect to proxy server\033[0m')
            clientsock.close()

    def on_close(self):
        # remove socket
        self.sockets.remove(self.s)
        self.sockets.remove(self.tx_chan[self.s])

        # closing socket channel
        out = self.tx_chan[self.s]
        self.tx_chan[out].close()
        self.rx_chan[out].close()
        self.tx_chan[self.s].close()
        self.rx_chan[self.s].close()

        # remove socket channel
        del self.tx_chan[out]
        del self.rx_chan[out]
        del self.tx_chan[self.s]
        del self.rx_chan[self.s]

    def on_execute(self):
        netdata = self.netdata

        # modify received netdata from injector server to sender socket
        try:
            netdata = netdata.decode('ascii')
            if netdata.find('CONNECT') == 0:
                req = netdata.split('HTTP')[0]
                req = req.split(' ')
                host_port = req[1].split(':')
                payloads = self.payload
                payloads = payloads.replace('[host_port]', req[1])
                payloads = payloads.replace('[host]', host_port[0])
                payloads = payloads.replace('[port]', host_port[1])
                if payloads.find('[split]') != -1:
                    pay = payloads.split('[split]')
                    self.request[self.tx_chan[self.s]] = pay[1]
                    netdata = pay[0]
                else:
                    netdata = payloads
                print('\033[93mConnecting\033[0m')
                logging.info('\033[93mConnecting\033[0m')
            netdata = netdata.encode('ascii')
        except Exception as e:
            # print(e)
            pass

        # print("Execute netdata: {}".format(netdata))

        try:
            self.tx_chan[self.s].send(netdata)
        except Exception as e:
            print(e)

    def on_outbounddata(self):
        netdata = self.netdata

        # modify received netdata from response
        try:
            netdata = netdata.decode('ascii')
            if netdata.find('HTTP/1.') == 0:
                if self.payload.find('[split]') != -1:
                    try:
                        if self.request[self.s] != '':
                            time.sleep(0.5)
                            self.request[self.s] = self.request[self.s].encode(
                                'ascii')
                            self.rx_chan[self.s].send(self.request[self.s])
                            self.request[self.s] = ''
                    except Exception as e:
                        print(e)
                netdata = 'HTTP/1.1 200 Connection established\r\n\r\n'
            netdata = netdata.encode('ascii')
        except Exception as e:
            # print(e)
            pass

        # print("Outbound netdata: {}".format(netdata))

        if 'zlib@openssh.com' in str(netdata):
            print('\033[92mConnected!\033[0m')
            logging.info('\033[92mConnected!\033[0m')

        try:
            self.tx_chan[self.s].send(netdata)
        except Exception as e:
            print(e)

    def main_loop(self):
        self.sockets.append(self.server)

        while True:
            ss = select.select
            i_sockets, _o_sockets, _e_sockets = ss(self.sockets, [], [])

            for self.s in i_sockets:
                if self.s == self.server:
                    # print('ACCEPT')
                    self.on_accept()
                    break

                try:
                    self.netdata = self.s.recv(self.buffer_size)
                except Exception as e:
                    print(e)
                    self.netdata = ''.encode('ascii')

                if len(self.netdata) <= 0:
                    print('\033[91mDisconnected!\033[0m')
                    logging.info('\033[91mDisconnected!\033[0m')
                    self.on_close()
                else:
                    if self.tx_chan[self.s] != self.rx_chan[self.s]:
                        # print('OUTBOUND')
                        self.on_outbounddata()
                    else:
                        # print('EXECUTE')
                        self.on_execute()


# initiate main program
if __name__ == '__main__':
    parser = argparse.ArgumentParser(
        prog='http-injector', description='Python Version of HTTP-INJECTOR')
    parser.add_argument('config',
                        metavar='payload',
                        type=argparse.FileType('r'),
                        help='payload file')
    parser.add_argument('-l',
                        dest='listen',
                        nargs='?',
                        const=9876,
                        help='listen port',
                        default=9876)
    args = parser.parse_args()

    server = Server(args.config, int(args.listen))

    try:
        server.main_loop()
    except KeyboardInterrupt:
        print("\033[0mCtrl C - Stopping server")
    except Exception as e:
        print(e)
        time.sleep(3)
        server.main_loop()

