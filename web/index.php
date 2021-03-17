<?php
    include('auth.php');
    check_session();
?>
<!doctype html>
<html lang="en">
<head>
    <?php
        $title = "Home";
        include("head.php");
    ?>
</head>
<body>
<div id="app">
    <?php include('navbar.php'); ?>
    <div class="container">
        <div class="row py-2">
            <div class="col-lg-8 col-md-12 mx-auto">
                <div class="text-center">
                    <h3>Home</h3>
                </div>
                <div class="card">
                    <div class="card-header">
                        <form @submit.prevent="runLibernet">
                            <div class="form-group form-row my-auto">
                                <label class="my-auto mx-1">Mode</label>
                                <select class="form-control w-25 mx-1" v-model.number="config.mode" :disabled="status === true" required>
                                    <option v-for="mode in config.temp.modes" :value="mode.value">{{ mode.name }}</option>
                                </select>
                                <label class="my-auto mx-1">Config</label>
                                <select class="form-control w-25 mx-1" v-model="config.profile" :disabled="status === true" required>
                                    <option v-for="profile in config.profiles" :value="profile">{{ profile }}</option>
                                </select>
                                <button type="submit" class="btn mx-1" :class="{ 'btn-danger': status, 'btn-primary': !status }">{{ statusText }}</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="card-body py-0">
                            <div class="row">
                                <div class="col-lg-6 col-md-6 pb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" v-model="config.system.tun2socks.legacy" :disabled="status === true" id="tun2socks-legacy">
                                        <label class="form-check-label" for="tun2socks-legacy">
                                            Use tun2socks legacy
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 pb-2">
                                    <div class="form-check float-right">
                                        <input class="form-check-input" type="checkbox" v-model="config.system.tunnel.autostart" :disabled="status === true" id="autostart">
                                        <label class="form-check-label" for="autostart">
                                            Auto start Libernet on boot
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-6 pb-2">
                                    <div class="form-check float-left">
                                        <input class="form-check-input" type="checkbox" v-model="config.system.tunnel.dns_resolver" :disabled="status === true" id="dns-resolver">
                                        <label class="form-check-label" for="dns-resolver">
                                            DNS resolver
                                        </label>
                                    </div>
                                    <div class="form-check float-right">
                                        <input class="form-check-input" type="checkbox" v-model="config.system.system.memory_cleaner" :disabled="status === true" id="memory-cleaner">
                                        <label class="form-check-label" for="memory-cleaner">
                                            Memory cleaner
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 row pb-2 pr-0">
                                    <div class="col-md-6">
                                        <div class="float-left">
                                            <span>Status: </span><span :class="{ 'text-secondary': connection === 0, 'text-warning': connection === 1, 'text-success': connection === 2, 'text-info': connection === 3 }">{{ connectionText }}</span>
                                            <span v-if="connection === 2" class="text-secondary">{{ connectedTime }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 pr-0 mr-0">
                                        <div class="float-right">
                                            <span>WAN IP: {{ wan_ip }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <pre ref="log" v-html="log" class="form-control text-left" style="height: 15rem; background-color: #e9ecef"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('footer.php'); ?>
    </div>
</div>
<?php include("javascript.php"); ?>
<script src="js/index.js"></script>
</body>
</html>