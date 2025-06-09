<?php
require_once 'config.php';
require_once 'sidebar.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !in_array($_SESSION['role'], [0, 2])) {
    header("Location: login.php");
    exit();
}

$message = '';
$selected_shelf_id = '';
$selected_product_id = '';
$selected_quantity = '1';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_shelf'])) {
        $shelf_identifier = $_POST['new_shelf_identifier'];

        try {
            $stmt = $pdo->prepare("INSERT INTO shelf_inventory (shelf_identifier) VALUES (?)");
            if ($stmt->execute([$shelf_identifier])) {
                $message = '<div class="success">Jauns plaukts veiksmīgi izveidots!</div>';
            } else {
                $message = '<div class="error">Kļūda izveidojot jaunu plauktu.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="error">Kļūda izveidojot jaunu plauktu: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } elseif (isset($_POST['update_shelf'])) {
        $shelf_id = $_POST['shelf_id'];
        $shelf_identifier = $_POST['shelf_identifier'];

        try {
            $stmt = $pdo->prepare("UPDATE shelf_inventory SET shelf_identifier = ? WHERE id = ?");
            if ($stmt->execute([$shelf_identifier, $shelf_id])) {
                $message = '<div class="success">Plaukta informācija veiksmīgi atjaunināta!</div>';
            } else {
                $message = '<div class="error">Kļūda atjauninot plaukta informāciju.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="error">Kļūda atjauninot plaukta informāciju: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } elseif (isset($_POST['add_product_to_shelf'])) {
        $shelf_id = $_POST['shelf_id'];
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];

        try {
            // First check if we have enough quantity available
            $stmt = $pdo->prepare("
                SELECT p.quantity as total_quantity,
                       COALESCE(SUM(sp.quantity), 0) as allocated_quantity
                FROM products p
                LEFT JOIN shelf_products sp ON p.id = sp.product_id
                WHERE p.id = ?
                GROUP BY p.id
            ");
            $stmt->execute([$product_id]);
            $quantity_info = $stmt->fetch();

            $available_quantity = $quantity_info['total_quantity'] - $quantity_info['allocated_quantity'];

            if ($quantity <= $available_quantity) {
                $stmt = $pdo->prepare("INSERT INTO shelf_products (shelf_id, product_id, quantity) VALUES (?, ?, ?) 
                                     ON DUPLICATE KEY UPDATE quantity = quantity + ?");
                if ($stmt->execute([$shelf_id, $product_id, $quantity, $quantity])) {
                    $message = '<div class="success">Prece veiksmīgi pievienota plauktam!</div>';
                } else {
                    $message = '<div class="error">Kļūda pievienojot preci plauktam.</div>';
                }
            } else {
                $message = '<div class="error">Nav pietiekami daudz preces pieejams. Pieejams: ' . $available_quantity . '</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="error">Kļūda pievienojot preci plauktam: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Get all products for dropdown with quantity information
$products = [];
try {
    $stmt = $pdo->query("
        SELECT p.id, p.name, p.price, p.quantity as total_quantity,
               COALESCE(SUM(sp.quantity), 0) as allocated_quantity
        FROM products p
        LEFT JOIN shelf_products sp ON p.id = sp.product_id
        GROUP BY p.id
        ORDER BY p.name
    ");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $message .= '<div class="error">Kļūda iegūstot preču sarakstu: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Get shelf inventory data
$inventory = [];
try {
    $stmt = $pdo->query("
        SELECT si.*, 
               GROUP_CONCAT(CONCAT(p.name, ' (', sp.quantity, ')') SEPARATOR ', ') as products_list,
               COUNT(sp.product_id) as product_count
        FROM shelf_inventory si
        LEFT JOIN shelf_products sp ON si.id = sp.shelf_id
        LEFT JOIN products p ON sp.product_id = p.id
        GROUP BY si.id
        ORDER BY si.shelf_identifier
    ");
    $inventory = $stmt->fetchAll();
} catch (PDOException $e) {
    $message .= '<div class="error">Kļūda iegūstot plauktu informāciju: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plauktu Kārtotājs</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

    <div class="container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <h1>Plauktu Kārtotāja Panelis</h1>

            <?php if ($message): ?>
                <?php echo $message; ?>
            <?php endif; ?>

            <div class="dashboard-cards">
                <div class="card">
                    <h3>Preču Izvietošana</h3>
                    <p>Pārvaldiet preču izvietojumu uz plauktiem</p>
                </div>

                <div class="card">
                    <h3>Atskaišu Sagatavošana</h3>
                    <p>Pārskatiet un sagatavojiet atskaites par plauktu stāvokli</p>
                </div>

                <div class="card">
                    <h3>Datu Ievade</h3>
                    <p>Atjauniniet informāciju par plauktos esošajām precēm</p>
                </div>
            </div>

            <div class="inventory-section">
                <h2>Plauktu Inventārs</h2>

                <?php if (!empty($inventory)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Plaukta ID</th>
                                <th>Plaukta Identifikators</th>
                                <th>Preces</th>
                                <th>Preču Skaits</th>
                                <th>Darbības</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory as $item) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['id']); ?></td>
                                <td><?php echo htmlspecialchars($item['shelf_identifier']); ?></td>
                                <td><?php echo htmlspecialchars($item['products_list'] ?? 'Nav preču'); ?></td>
                                <td><?php echo htmlspecialchars($item['product_count']); ?></td>
                                <td>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="shelf_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                        <input type="hidden" name="shelf_identifier" value="<?php echo htmlspecialchars($item['shelf_identifier']); ?>">
                                        <button type="submit" name="edit_shelf" class="btn-edit">Labot</button>
                                    </form>
                                </td>
                            </tr>
                            <?php } // End foreach ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nav pievienotu plauktu ierakstu.</p>
                <?php endif; ?>

                <div class="forms-container">
                    <div class="form-section">
                        <h3>Izveidot Jaunu Plauktu</h3>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="new_shelf_identifier">Plaukta Identifikators:</label>
                                <input type="text" id="new_shelf_identifier" name="new_shelf_identifier" required>
                            </div>

                            <button type="submit" name="create_shelf" class="btn-primary">Izveidot</button>
                        </form>
                    </div>

                    <?php if (isset($_POST['edit_shelf'])): ?>
                    <div class="form-section">
                        <h3>Labot Plaukta Informāciju</h3>
                        <form method="POST" action="">
                            <input type="hidden" name="shelf_id" value="<?php echo htmlspecialchars($_POST['shelf_id']); ?>">

                            <div class="form-group">
                                <label for="shelf_identifier">Plaukta Identifikators:</label>
                                <input type="text" id="shelf_identifier" name="shelf_identifier" value="<?php echo htmlspecialchars($_POST['shelf_identifier']); ?>" required>
                            </div>

                            <button type="submit" name="update_shelf" class="btn-primary">Saglabāt</button>
                        </form>
                    </div>
                    <?php endif; ?>

                    <div class="form-section">
                        <h3>Pievienot Preci Plauktam</h3>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="shelf_id">Plaukts:</label>
                                <select id="shelf_id" name="shelf_id" required>
                                    <option value="">Izvēlieties plauktu</option>
                                    <?php foreach ($inventory as $shelf): ?>
                                        <option value="<?php echo htmlspecialchars($shelf['id']); ?>" <?php echo $selected_shelf_id == $shelf['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($shelf['shelf_identifier']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="product_id">Prece:</label>
                                <select id="product_id" name="product_id" required>
                                    <option value="">Izvēlieties preci</option>
                                    <?php foreach ($products as $product): 
                                        $available = $product['total_quantity'] - $product['allocated_quantity'];
                                    ?>
                                        <option value="<?php echo htmlspecialchars($product['id']); ?>" <?php echo $selected_product_id == $product['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($product['name']); ?> 
                                            (Pieejams: <?php echo $available; ?>, Cena: <?php echo htmlspecialchars($product['price']); ?> €)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="quantity">Daudzums:</label>
                                <input type="number" id="quantity" name="quantity" min="1" value="<?php echo htmlspecialchars($selected_quantity); ?>" required>
                            </div>

                            <button type="submit" name="add_product_to_shelf" class="btn-primary">Pievienot</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 