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
                $_SESSION['role'] = $user['role']; // Add this if you want to use role in sidebar
                $_SESSION['logged_in'] = true;     // <-- Added line
                $message = 'Pieslēgšanās veiksmīga!';
                // Redirect to dashboard or home page after successful login
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

</head>
<body>
<div class="container">
    <div class="sidebar">
        <h1><span>🏠</span>STASH</h1>
        <div style="margin-top:40px; color:#222;">
            <div><b>Pieslēgšanās</b></div>
            <div style="margin-top:10px; font-size:0.95em;">Ievadiet savus datus</div>
        </div>
    </div>
    <div class="main">
        <form class="login-form" method="post">
            <h2>Pieslēgties</h2>
            <?php if ($message): ?>
                <div class="message<?php if ($message === 'Pieslēgšanās veiksmīga!') echo ' success'; ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <label for="username">Lietotājvārds</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Parole</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Pieslēgties</button>
            <div style="text-align:center; margin-top:12px;">
                <a href="register.php" style="color:#555; background:none; padding:0; border-radius:0; text-decoration:underline; font-weight:normal; font-size:0.97em; display:inline-block;">Izveidot jaunu kontu</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>