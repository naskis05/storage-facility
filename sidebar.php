<?php
session_start(); // <-- Add this line at the very top
require_once 'config.php';
?>

<!-- Sidebar HTML -->
 <link rel="stylesheet" href="style.css">
<div class="sidebar">
    <h2>Sānu izvēlne</h2>

    <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
        <p>Lūdzu, <a href="login.php">piesakieties</a>.</p>

    <?php else: ?>
        <p><strong>Lietotājs:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        <p><strong>Loma:</strong>
            <?php
            switch ($_SESSION['role']) {
                case 0: echo "Administrators"; break;
                case 1: echo "Noliktavas darbinieks"; break;
                case 2: echo "Plauktu kārtotājs"; break;
                default: echo "Nezināma loma";
            }
            ?>
        </p>

        <ul>
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

        <p><a href="logout.php">Izrakstīties</a></p>
    <?php endif; ?>
</div>
