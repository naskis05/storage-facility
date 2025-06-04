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
    <style>
        body {
            margin: 0;
            background: #FFFFFF; /* White main background */
            font-family: Arial, sans-serif;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            background: #8D6E63; /* Brown/Taupe */
            width: 320px;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            color: #FFFFFF; /* White text for contrast */
        }
        .sidebar h1 {
            font-size: 2em;
            margin: 0 0 20px 0;
            font-weight: bold;
            display: flex;
            align-items: center;
            color: #FFFFFF; /* White text */
        }
        .sidebar h1 span {
            margin-right: 10px;
        }
        .main {
            flex: 1;
            background: #FFFFFF; /* White background */
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-form {
            background: #FFFFFF; /* White form background */
            padding: 32px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06); /* Keep a subtle shadow */
            min-width: 320px;
        }
        .login-form h2 {
            margin-top: 0;
            margin-bottom: 24px;
            font-size: 1.5em;
            color: #222; /* Dark text */
        }
        .login-form label {
            display: block;
            margin-bottom: 6px;
            color: #222; /* Dark text */
            font-weight: 500;
        }
        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 18px;
            border: 1px solid #ccc; /* Light grey border */
            border-radius: 4px;
            background: #FFFFFF; /* White background */
            font-size: 1em;
            color: #222; /* Dark text */
        }
        .login-form button {
            width: 100%;
            padding: 10px;
            background: #FFFFFF; /* White button background */
            color: #222; /* Dark text */
            border: 1px solid #8D6E63; /* Brown border */
            border-radius: 4px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .login-form button:hover {
            background: #FFA55D; /* Orange hover background */
            color: #FFFFFF; /* White text on hover */
            border-color: #FFA55D; /* Orange border on hover */
        }
        .message {
            margin-bottom: 16px;
            color: #b00; /* Red for errors */
            font-weight: bold;
        }
        .success {
            color: #2b7a2b; /* Green for success */
        }
         .login-form a {
             color:#222; /* Dark text for links */
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