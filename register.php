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
    <style>
        body {
            margin: 0;
            background: #FFFFFF;
            font-family: Arial, sans-serif;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            background: #8D6E63;
            width: 320px;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            color: #FFFFFF;
        }
        .sidebar h1 {
            font-size: 2em;
            margin: 0 0 20px 0;
            font-weight: bold;
            display: flex;
            align-items: center;
            color: #FFFFFF;
        }
        .sidebar h1 span {
            margin-right: 10px;
        }
        .main {
            flex: 1;
            background: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-form {
            background: #FFFFFF;
            padding: 32px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            min-width: 320px;
        }
        .register-form h2 {
            margin-top: 0;
            margin-bottom: 24px;
            font-size: 1.5em;
            color: #222;
        }
        .register-form label {
            display: block;
            margin-bottom: 6px;
            color: #222;
            font-weight: 500;
        }
        .register-form input[type="text"],
        .register-form input[type="password"],
        .register-form input[type="email"] {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #FFFFFF;
            font-size: 1em;
            color: #222;
        }
        .register-form button {
            width: 100%;
            padding: 10px;
            background: #FFFFFF;
            color: #222;
            border: 1px solid #8D6E63;
            border-radius: 4px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .register-form button:hover {
            background: #FFA55D;
            color: #FFFFFF;
             border-color: #FFA55D;
        }
        .message {
            margin-bottom: 16px;
            color: #b00;
            font-weight: bold;
        }
        .success {
            color: #2b7a2b;
        }
        .register-form a {
             color:#222;
             background:none;
             padding:0;
             border-radius:0;
             text-decoration:underline;
             font-weight:normal;
             font-size:0.97em;
             display:inline-block;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h1><span>🏠</span>STASH</h1>
        <div style="margin-top:40px; color:#222;">
            <div><b>Reģistrācija</b></div>
            <div style="margin-top:10px; font-size:0.95em;">Lūdzu, izveidojiet savu kontu</div>
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
            <label for="username">Lietotājvārds</label>
            <input type="text" id="username" name="username" required maxlength="20" onkeypress="return event.charCode != 32">

            <label for="email">E-pasts</label>
            <input type="email" id="email" name="email" required onkeypress="return event.charCode != 32">

            <label for="password">Parole</label>
            <input type="password" id="password" name="password" required maxlength="20" onkeypress="return event.charCode != 32">

            <label for="confirm">Apstipriniet paroli</label>
            <input type="password" id="confirm" name="confirm" required maxlength="20" onkeypress="return event.charCode != 32">

            <button type="submit">Reģistrēties</button>
            <div style="text-align:center; margin-top:12px;">
                <a href="login.php" style="color:#555; background:none; padding:0; border-radius:0; text-decoration:underline; font-weight:normal; font-size:0.97em; display:inline-block;">Man jau ir konts</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>