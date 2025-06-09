<?php
require_once 'config.php';
require_once 'sidebar.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 1) {
    header("Location: login.php");
    exit();
}

$message = '';

// Fetch data for forms and display
$products = [];
try {
    $stmt = $pdo->query("SELECT id, name, price, quantity FROM products ORDER BY name");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message .= '<div class="error">Error fetching products: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

$orders = [];
try {
    $stmt = $pdo->query("
        SELECT o.*, u.username, 
               GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ')') SEPARATOR ', ') as order_items_list
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message .= '<div class="error">Error fetching orders: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_order'])) {
        $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
        $product_ids = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];

        if (empty($product_ids)) {
            $message = '<div class="error">Order must contain at least one product.</div>';
        } else {
            try {
                $pdo->beginTransaction();

                // Create the order
                $stmt = $pdo->prepare("INSERT INTO orders (user_id) VALUES (?)");
                $stmt->execute([$user_id]);
                $order_id = $pdo->lastInsertId();

                foreach ($product_ids as $index => $product_id) {
                    $quantity = $quantities[$index];
                    if ($quantity <= 0) continue;

                    // Get product price and check stock
                    $stmt = $pdo->prepare("SELECT name, price, quantity FROM products WHERE id = ?");
                    $stmt->execute([$product_id]);
                    $product_info = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$product_info || $product_info['quantity'] < $quantity) {
                        throw new Exception('Insufficient stock for product ' . htmlspecialchars($product_info['name'] ?? 'ID ' . $product_id));
                    }

                    // Add to order_items
                    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_order) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$order_id, $product_id, $quantity, $product_info['price']]);

                    // Deduct from product stock
                    $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                    $stmt->execute([$quantity, $product_id]);
                }

                $pdo->commit();
                $message = '<div class="success">Order created successfully!</div>';
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = '<div class="error">Failed to create order: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
    } elseif (isset($_POST['update_order_status'])) {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];

        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $order_id])) {
                $message = '<div class="success">Order status updated successfully!</div>';
            } else {
                $message = '<div class="error">Error updating order status.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="error">Error updating order status: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasūtījumu Pārvaldība</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

    <div class="container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <h1>Pasūtījumu Pārvaldība</h1>

            <?php if ($message): ?>
                <?php echo $message; ?>
            <?php endif; ?>

            <div class="forms-container">
                <div class="form-section">
                    <h3>Izveidot Jaunu Pasūtījumu</h3>
                    <form method="POST" action="">
                        <div id="product-entries">
                            <div class="form-group product-entry">
                                <label for="product_id_0">Prece:</label>
                                <select name="product_id[]" id="product_id_0" required>
                                    <option value="">Izvēlieties preci</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo htmlspecialchars($product['id']); ?>">
                                            <?php echo htmlspecialchars($product['name']); ?> (Pieejams: <?php echo htmlspecialchars($product['quantity']); ?>, Cena: <?php echo htmlspecialchars($product['price']); ?> €)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="quantity_0">Daudzums:</label>
                                <input type="number" name="quantity[]" id="quantity_0" min="1" value="1" required>
                            </div>
                        </div>
                        <button type="button" class="btn-add" onclick="addProductEntry()">Pievienot citu preci</button>
                        <button type="submit" name="create_order" class="btn-primary">Izveidot Pasūtījumu</button>
                    </form>
                </div>
            </div>

            <h2>Esošie Pasūtījumi</h2>
            <?php if (!empty($orders)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Pasūtījuma ID</th>
                            <th>Lietotājs</th>
                            <th>Preces</th>
                            <th>Statuss</th>
                            <th>Izveidots</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td><?php echo htmlspecialchars($order['order_items_list'] ?? 'No items'); ?></td>
                                <td><?php echo htmlspecialchars($order['status']); ?></td>
                                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                                <td>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="accepted" <?php echo $order['status'] == 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                                            <option value="fulfilled" <?php echo $order['status'] == 'fulfilled' ? 'selected' : ''; ?>>Fulfilled</option>
                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_order_status" value="1">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No orders found.</p>
            <?php endif; ?>

        </div>
    </div>

    <script>
        let productEntryCount = 1;

        function addProductEntry() {
            const productEntriesDiv = document.getElementById('product-entries');
            const newEntry = document.createElement('div');
            newEntry.classList.add('form-group', 'product-entry');
            newEntry.innerHTML = `
                <label for="product_id_${productEntryCount}">Prece:</label>
                <select name="product_id[]" id="product_id_${productEntryCount}" required>
                    <option value="">Izvēlieties preci</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo htmlspecialchars($product['id']); ?>">
                            <?php echo htmlspecialchars($product['name']); ?> (Pieejams: <?php echo htmlspecialchars($product['quantity']); ?>, Cena: <?php echo htmlspecialchars($product['price']); ?> €)
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="quantity_${productEntryCount}">Daudzums:</label>
                <input type="number" name="quantity[]" id="quantity_${productEntryCount}" min="1" value="1" required>
                <button type="button" onclick="removeProductEntry(this)" class="btn-danger">Noņemt</button>
            `;
            productEntriesDiv.appendChild(newEntry);
            productEntryCount++;
        }

        function removeProductEntry(button) {
            button.parentNode.remove();
        }
    </script>
</body>
</html> 