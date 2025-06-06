<?php
session_start(); // <-- Add this line at the very top
require_once 'config.php';
?>

<!-- Sidebar HTML -->
<link rel="stylesheet" href="style.css">
<div class="sidebar">
    <h2><span>🏠</span>STASH</h2>

    <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
        <div style="margin-top:40px; color:#FFFFFF;">
            <div><b>Pieslēgšanās</b></div>
            <div style="margin-top:10px; font-size:0.95em;">Lūdzu, piesakieties</div>
        </div>

    <?php else: ?>
        <div style="margin-top:40px; color:#FFFFFF;">
            <div><b><?php echo htmlspecialchars($_SESSION['username']); ?></b></div>
            <div style="margin-top:10px; font-size:0.95em;">
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

        <ul style="margin-top: 40px;">
            <?php if ($_SESSION['role'] === 0): ?>
                <li><a href="index.php">Pārskats</a></li>
                <li><a href="lietotaji.php">Lietotāju pārvaldība</a></li>
                <li><a href="iestatijumi.php">Sistēmas iestatījumi</a></li>

            <?php elseif ($_SESSION['role'] === 1): ?>
                <li><a href="preces.php">Preču saraksts</a></li>
                <li><a href="pasutijumi.php">Pasūtījumi</a></li>

            <?php elseif ($_SESSION['role'] === 2): ?>
                <li><a href="plaukti.php">Plauktu pārvaldība</a></li>
            <?php endif; ?>
        </ul>

        <div style="margin-top: 40px;">
            <a href="logout.php">Izrakstīties</a>
        </div>
    <?php endif; ?>
</div>
