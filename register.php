<?php
require_once 'config.php';

session_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $message = 'Lūdzu, aizpildiet visus laukus.';
    } elseif (strpos($username, ' ') !== false) {
        $message = 'Lietotājvārdā nedrīkst būt atstarpes.';
    } elseif (!preg_match('/^[a-zA-Z0-9]*$/', $username)) {
        $message = 'Lietotājvārdā drīkst izmantot tikai burtus un ciparus.';
    } elseif (strlen($username) < 6 || strlen($username) > 20) {
        $message = 'Lietotājvārdam jābūt vismaz 8 un maksimāli 20 simbolus garš.';
    } elseif (strpos($password, ' ') !== false) {
        $message = 'Parolē nedrīkst būt atstarpes.';
    } elseif (strpos($email, ' ') !== false) {
        $message = 'E-pasta adresē nedrīkst būt atstarpes.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Nederīgs e-pasta formāts.';
    } elseif ($password !== $confirm) {
        $message = 'Paroles nesakrīt.';
    } elseif (strlen($password) < 8 || strlen($password) > 20) {
        $message = 'Parolei jābūt vismaz 8 un maksimāli 20 simbolus garš.';
    } elseif (str_replace('.', '', $password) === '') {
        $message = 'Nederīgs paroles formāts.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $message = 'Lietotājvārds vai e-pasts jau eksistē.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $created_at = date('Y-m-d H:i:s');

                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$username, $email, $hashed_password, $created_at])) {
                    $message = 'Reģistrācija veiksmīga!';
                    header("Location: login.php");
                    exit();
                } else {
                    $message = 'Kļūda reģistrējot lietotāju.';
                }
            }
        } catch (PDOException $e) {
            $message = 'Kļūda reģistrējot lietotāju: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Reģistrācija | STASH</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h1><span>🏠</span>STASH</h1>
        <div class="sidebar-register-info">
            <div><b>Reģistrācija</b></div>
            <div class="sidebar-register-desc">Lūdzu, izveidojiet savu kontu</div>
        </div>
    </div>
    <div class="main">
        <form class="register-form" method="post">
            <h2>Izveidot kontu</h2>
            <?php if ($message): ?>
                <div class="message<?php if ($message === 'Reģistrācija veiksmīga!') echo ' success'; ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="username">Lietotājvārds</label>
                <input type="text" id="username" name="username" class="form-control">
            </div>

            <div class="form-group">
                <label for="email">E-pasts</label>
                <input type="email" id="email" name="email" class="form-control">
            </div>

            <div class="form-group">
                <label for="password">Parole</label>
                <input type="password" id="password" name="password" class="form-control">
            </div>

            <div class="form-group">
                <label for="confirm">Apstipriniet paroli</label>
                <input type="password" id="confirm" name="confirm" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Reģistrēties</button>
            <div class="form-text-center">
                <a href="login.php" class="auth-form-link">Man jau ir konts</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>