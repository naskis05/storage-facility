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
$message = '';
if (isset($_POST['edit_user'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = (int)($_POST['role'] ?? 1);
    if ($username === '' || $email === '') {
        $message = '<div class="error">Aizpildi visus laukus!</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="error">Nederīgs e-pasts!</div>';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $message = '<div class="error">Lietotājvārdam jābūt 3-20 simboli!</div>';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?');
        $stmt->execute([$username, $email, $user_id]);
        if ($stmt->fetchColumn() > 0) {
            $message = '<div class="error">Lietotājs ar šādu e-pastu vai lietotājvārdu jau eksistē!</div>';
        } else {
            $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?');
            if ($stmt->execute([$username, $email, $role, $user_id])) {
                $message = '<div class="success">Lietotājs atjaunināts!</div>';
            } else {
                $message = '<div class="error">Kļūda saglabājot!</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Rediģēt lietotāju</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main-content">
    <div class="admin-panel">
        <h2>Rediģēt lietotāju</h2>
        <?php if ($message) echo $message; ?>
        <form method="post" class="user-form">
            <div class="form-group">
                <label>Lietotājvārds</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>">
            </div>
            <div class="form-group">
                <label>E-pasts</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            <div class="form-group">
                <label>Loma</label>
                <select name="role" class="form-control">
                    <option value="0"<?= $user['role']==0?' selected':''; ?>>Administrators</option>
                    <option value="1"<?= $user['role']==1?' selected':''; ?>>Noliktavas darbinieks</option>
                    <option value="2"<?= $user['role']==2?' selected':''; ?>>Plauktu kārtotājs</option>
                </select>
            </div>
            <button type="submit" name="edit_user" class="btn btn-primary">Saglabāt</button>
            <a href="lietotaji.php" class="btn" style="background:#888; color:#fff; margin-left:8px;">Atpakaļ</a>
        </form>
    </div>
</div>
</body>
</html> 