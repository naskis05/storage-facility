<?php
require_once 'config.php';
require_once 'sidebar.php';


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !in_array($_SESSION['role'], [0, 2])) {
    header("Location: login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_shelf']) && in_array($_SESSION['role'], [0, 2])) {
    $shelf_identifier = trim($_POST['shelf_identifier'] ?? '');


    if ($shelf_identifier === '') {
        $message = '<div class="error">Lūdzu, ievadiet plaukta kodu/nosaukumu.</div>';
    } elseif (strlen($shelf_identifier) < 2 || strlen($shelf_identifier) > 50) {
         $message = '<div class="error">Plaukta kodam/nosaukumam jābūt no 2 līdz 50 simboliem.</div>';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM shelf_inventory WHERE shelf_identifier = ?");
            $stmt->execute([$shelf_identifier]);
            if ($stmt->fetchColumn() > 0) {
                $message = '<div class="error">Plaukts ar šādu kodu/nosaukumu jau eksistē.</div>';
            } else {
                $stmt = $pdo->prepare("INSERT INTO shelf_inventory (shelf_identifier) VALUES (?)");
                if ($stmt->execute([$shelf_identifier])) {
                    $message = '<div class="success">Plaukts veiksmīgi pievienots!</div>';
                } else {
                    $message = '<div class="error">Kļūda pievienojot plauktu.</div>';
                }
            }
        } catch (PDOException $e) {
            $message = '<div class="error">Kļūda datubāzē: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Plauktu pārvaldība</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main-content">
    <div class="admin-panel">
        <h2>Plauktu pārvaldība</h2>

        <?php if ($message) echo $message; ?>

        <?php if (in_array($_SESSION['role'], [0, 2])): // Only show form for Admin and Plauktu kārtotājs ?>
            <div class="product-form user-form">
                <h3>Pievienot jaunu plauktu</h3>
                <form method="post">
                    <div class="form-group">
                        <label for="shelf_identifier">Plaukta kods/Nosaukums</label>
                        <input type="text" id="shelf_identifier" name="shelf_identifier" class="form-control" required placeholder="Ievadiet plaukta kodu vai nosaukumu">
                    </div>
                    <button type="submit" name="add_shelf" class="btn btn-primary">Pievienot plauktu</button>
                </form>
            </div>
        <?php endif; ?>

        <h3>Esošie plaukti</h3>
        <?php
        try {
            $stmt = $pdo->query("SELECT id, shelf_identifier, created_at FROM shelf_inventory ORDER BY created_at DESC");
            $shelves = $stmt->fetchAll();

            if ($shelves) {
                echo '<table class="products-table">';
                echo '<tr><th>ID</th><th>Kods/Nosaukums</th><th>Izveidots</th></tr>';

                foreach ($shelves as $shelf) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($shelf['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($shelf['shelf_identifier']) . '</td>';
                    echo '<td>' . htmlspecialchars($shelf['created_at']) . '</td>
';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p>Nav pievienotu plauktu.</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="error">Kļūda iegūstot plauktu informāciju: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>

    </div>
</div>
</body>
</html> 