<?php
    $url_array =  explode('/', $_SERVER['REQUEST_URI']) ;
    $url = end($url_array);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand" href="#">Libernet</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item <?php if ($url === 'index.php') echo 'active'; ?>">
                <a class="nav-link" href="index.php"><i class="fa fa-home"></i> Home <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item <?php if ($url === 'config.php') echo 'active'; ?>">
                <a class="nav-link" href="config.php"><i class="fa fa-gears"></i> Configuration</a>
            </li>
            <li class="nav-item <?php if ($url === 'system.php') echo 'active'; ?>">
                <a class="nav-link" href="system.php"><i class="fa fa-user"></i> System</a>
            </li>
            <li class="nav-item <?php if ($url === 'speedtest.php') echo 'active'; ?>">
                <a class="nav-link" href="speedtest.php"><i class="fa fa-arrows-v"></i> SpeedTest</a>
            </li>
            <li class="nav-item <?php if ($url === 'about.php') echo 'active'; ?>">
                <a class="nav-link" href="about.php"><i class="fa fa-info"></i> About</a>
            </li>
        </ul>
        <form action="logout.php">
            <button type="submit" class="btn btn-primary">Logout <i class="fa fa-sign-out"></i></button>
        </form>
    </div>
</nav>