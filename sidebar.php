<?php
session_start();
?>

<div id="sidebar-container">
    <h3>Sānu izvēlne</h3>

    <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
        <p>Lūdzu, <a href="login.php">piesakieties</a>, lai skatītu saturu.</p>

    <?php else: ?>
        <p><strong>Lietotāja tips:</strong></p>

        <?php if ($_SESSION['role'] === 0): ?>
            <p>Administrators</p>
            <ul>
                <li><a href="dashboard.php">Pārskats</a></li>
                <li><a href="lietotaji.php">Lietotāju pārvaldība</a></li>
                <li><a href="iestatijumi.php">Sistēmas iestatījumi</a></li>
            </ul>

        <?php elseif ($_SESSION['role'] === 1): ?>
            <p>Noliktavas darbinieks</p>
            <ul>
                <li><a href="preces.php">Preču saraksts</a></li>
                <li><a href="pasutijumi.php">Pasūtījumi</a></li>
            </ul>

        <?php elseif ($_SESSION['role'] === 2): ?>
            <p>Plauktu kārtotājs</p>
            <ul>
                <li><a href="plaukti.php">Plauktu pārvaldība</a></li>
            </ul>

        <?php else: ?>
            <p>Nezināms lietotāja tips</p>
        <?php endif; ?>

        <p><a href="logout.php">Izrakstīties</a></p>
    <?php endif; ?>
</div>
