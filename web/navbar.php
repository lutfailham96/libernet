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
                <a class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item <?php if ($url === 'config.php') echo 'active'; ?>">
                <a class="nav-link" href="config.php">Configuration</a>
            </li>
            <li class="nav-item <?php if ($url === 'system.php') echo 'active'; ?>">
                <a class="nav-link" href="system.php">System</a>
            </li>
            <li class="nav-item <?php if ($url === 'about.php') echo 'active'; ?>">
                <a class="nav-link" href="about.php">About</a>
            </li>
        </ul>
        <form action="logout.php">
            <button type="submit" class="btn btn-primary">Logout</button>
        </form>
    </div>
</nav>