<?php
require_once 'config.php';

session_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $message = 'Lūdzu, aizpildiet visus laukus.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                $message = 'Pieslēgšanās veiksmīga!';
                header("Location: index.php");
                exit();
            } else {
                $message = 'Nepareizs lietotājvārds vai parole.';
            }
        } catch (PDOException $e) {
            $message = 'Kļūda pieslēdzoties: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Pieslēgties | STASH</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="sidebar">
    <h2><span>🏠</span>STASH</h2>
    <div class="sidebar-login-info">
        <div><b>Pieslēgšanās</b></div>
        <div class="sidebar-login-desc">Ievadiet savus datus</div>
    </div>
</div>
<div class="main-content">
    <div class="admin-panel">
        <form class="login-form" method="post">
            <h2>Pieslēgties</h2>
            <?php if ($message): ?>
                <div class="message<?php if ($message === 'Pieslēgšanās veiksmīga!') echo ' success'; ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="username">Lietotājvārds</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Parole</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Pieslēgties</button>
            <div style="text-align:center; margin-top:12px;">
                <a href="register.php">Izveidot jaunu kontu</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>