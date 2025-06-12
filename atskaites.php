<?php
require_once 'config.php';
require_once 'sidebar.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}


$report = [];
try {
    $stmt = $pdo->query("
        SELECT si.shelf_identifier, p.name AS product_name, sp.quantity
        FROM shelf_products sp
        JOIN shelf_inventory si ON sp.shelf_id = si.id
        JOIN products p ON sp.product_id = p.id
        ORDER BY si.shelf_identifier, p.name
    ");
    $report = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Kļūda iegūstot atskaites datus: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Atskaišu sagatavošana</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main-content">
    <h2>Atskaišu sagatavošana</h2>
    <p>Sagatavo preces atskaitēm un pārskatiem, informējot noliktavas vadību par plauktos esošo preču stāvokli.</p>
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <table class="products-table">
        <thead>
            <tr>
                <th>Plaukta identifikators</th>
                <th>Preces nosaukums</th>
                <th>Daudzums plauktā</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($report as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['shelf_identifier']); ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html> 