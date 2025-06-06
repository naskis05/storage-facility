<?php
session_start();
require_once 'config.php';
?>


<link rel="stylesheet" href="style.css">
<div class="sidebar">
    <h2><span>🏠</span>STASH</h2>

    <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
        <div class="sidebar-login-info">
            <div><b>Pieslēgšanās</b></div>
            <div class="sidebar-login-desc">Lūdzu, piesakieties</div>
            <a href="login.php" class="btn btn-primary sidebar-login-btn" style="margin-top:18px;">Log in</a>
        </div>

    <?php else: ?>
        <div class="sidebar-user-info">
            <div><b><?php echo htmlspecialchars($_SESSION['username']); ?></b></div>
            <div class="sidebar-user-role">
                <?php
                switch ($_SESSION['role']) {
                    case 0: echo "Administrators"; break;
                    case 1: echo "Noliktavas darbinieks"; break;
                    case 2: echo "Plauktu kārtotājs"; break;
                    default: echo "Nezināma loma";
                }
                ?>
            </div>
        </div>

        <ul class="sidebar-menu">
            <?php if ($_SESSION['role'] === 0): ?>
                <li><a href="index.php">Pārskats</a></li>
                <li><a href="lietotaji.php">Lietotāju pārvaldība</a></li>
                <li><a href="preces.php">Preču pārvaldība</a></li>
                <li><a href="plauktu_kartotajs.php">Plauktu kārtošana</a></li>
                <li><a href="iestatijumi.php">Sistēmas iestatījumi</a></li>


            <?php elseif ($_SESSION['role'] === 1): ?>
                <li><a href="preces.php">Preču pārvaldība</a></li>
                <li><a href="pasutijumi.php">Pasūtījumi</a></li>

            <?php elseif ($_SESSION['role'] === 2): ?>
                <li><a href="plauktu_kartotajs.php">Plauktu pārvaldība</a></li>
                <li><a href="atskaites.php">Atskaišu sagatavošana</a></li>
            <?php endif; ?>
        </ul>

        <div class="sidebar-logout">
            <a href="logout.php">Izrakstīties</a>
        </div>
    <?php endif; ?>
</div>