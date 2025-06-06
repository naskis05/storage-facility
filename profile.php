<?php
require_once 'config.php';
require_once 'sidebar.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 0) {
    header('Location: lietotaji.php');
    exit();
}
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$user_id) {
    echo '<div class="main-content"><div class="admin-panel"><div class="error">Lietotājs nav atrasts!</div></div></div>';
    exit();
}
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) {
    echo '<div class="main-content"><div class="admin-panel"><div class="error">Lietotājs nav atrasts!</div></div></div>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Lietotāja profils</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main-content">
    <div class="admin-panel">
        <h2>Lietotāja profils</h2>
        <div class="form-group"><b>Lietotājvārds:</b> <?= htmlspecialchars($user['username']) ?></div>
        <div class="form-group"><b>E-pasts:</b> <?= htmlspecialchars($user['email']) ?></div>
        <div class="form-group"><b>Loma:</b> <?php
            switch ($user['role']) {
                case 0: echo 'Administrators'; break;
                case 1: echo 'Noliktavas darbinieks'; break;
                case 2: echo 'Plauktu kārtotājs'; break;
                default: echo 'Nezināma loma';
            }
        ?></div>
        <div class="form-group"><b>Izveidots:</b> <?= htmlspecialchars($user['created_at']) ?></div>
        <a href="lietotaji.php" class="btn" style="background:#888; color:#fff;">Atpakaļ</a>
    </div>
</div>
</body>
</html> 