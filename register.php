<?php
<!-- filepath: c:\xampp\htdocs\storage-facility\register.php -->
<?php
// Simple registration logic (no database, just for demo)
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    if ($username === '' || $password === '' || $confirm === '') {
        $message = 'Lūdzu, aizpildiet visus laukus.';
    } elseif ($password !== $confirm) {
        $message = 'Paroles nesakrīt.';
    } else {
        $message = 'Reģistrācija veiksmīga!';
        // Here you would normally save to a database
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
            background: #222;
            font-family: Arial, sans-serif;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            background: #c3caae;
            width: 320px;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .sidebar h1 {
            font-size: 2em;
            margin: 0 0 20px 0;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        .sidebar h1 span {
            margin-right: 10px;
        }
        .main {
            flex: 1;
            background: #f7f7f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-form {
            background: #fff;
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
        .register-form input[type="password"] {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 18px;
            border: 1px solid #c3caae;
            border-radius: 4px;
            background: #f7f7f5;
            font-size: 1em;
        }
        .register-form button {
            width: 100%;
            padding: 10px;
            background: #c3caae;
            color: #222;
            border: none;
            border-radius: 4px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .register-form button:hover {
            background: #b0b89c;
        }
        .message {
            margin-bottom: 16px;
            color: #b00;
            font-weight: bold;
        }
        .success {
            color: #2b7a2b;
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
            <input type="text" id="username" name="username" required>

            <label for="password">Parole</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm">Apstipriniet paroli</label>
            <input type="password" id="confirm" name="confirm" required>

            <button type="submit">Reģistrēties</button>
            <div style="text-align:center; margin-top:12px;">
                <a href="login.php" style="color:#555; background:none; padding:0; border-radius:0; text-decoration:underline; font-weight:normal; font-size:0.97em; display:inline-block;">Man jau ir konts</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>