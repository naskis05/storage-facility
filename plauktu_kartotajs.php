<?php
require_once 'config.php';
require_once 'sidebar.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !in_array($_SESSION['role'], [0, 2])) {
    header("Location: login.php");
    exit();
}

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_shelf'])) {
        $shelf_id = $_POST['shelf_id'];
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $location = $_POST['location'];

        try {
            $stmt = $pdo->prepare("UPDATE shelf_inventory SET quantity = ?, location = ? WHERE shelf_id = ? AND product_id = ?");
            if ($stmt->execute([$quantity, $location, $shelf_id, $product_id])) {
                $message = '<div class="success">Plaukta informācija veiksmīgi atjaunināta!</div>';
            } else {
                $message = '<div class="error">Kļūda atjauninot plaukta informāciju.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="error">Kļūda atjauninot plaukta informāciju: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Get shelf inventory data
$inventory = [];
try {
    $stmt = $pdo->query("
        SELECT si.*, p.name as product_name, p.sku
        FROM shelf_inventory si
        JOIN products p ON si.product_id = p.id
        ORDER BY si.shelf_id, p.name
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
                                <th>Preces Nosaukums</th>
                                <th>SKU</th>
                                <th>Daudzums</th>
                                <th>Izvietojums</th>
                                <th>Darbības</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory as $item) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['shelf_id']); ?></td>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['sku']); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($item['location']); ?></td>
                                <td>
                                    <button class="btn-edit" onclick="editShelfItem(<?php echo htmlspecialchars(json_encode($item['shelf_id'])); ?>, <?php echo htmlspecialchars(json_encode($item['product_id'])); ?>)">Labot</button>
                                </td>
                            </tr>
                            <?php } // End foreach ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nav pievienotu plauktu ierakstu.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Labot Plaukta Preci</h2>
            <form method="POST" action="">
                <input type="hidden" name="shelf_id" id="edit_shelf_id">
                <input type="hidden" name="product_id" id="edit_product_id">

                <div class="form-group">
                    <label for="quantity">Daudzums:</label>
                    <input type="number" id="quantity" name="quantity" required>
                </div>

                <div class="form-group">
                    <label for="location">Izvietojums:</label>
                    <input type="text" id="location" name="location" required>
                </div>

                <button type="submit" name="update_shelf" class="btn-primary">Saglabāt</button>
            </form>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById('editModal');
        const span = document.getElementsByClassName('close')[0];

        function editShelfItem(shelfId, productId) {
            document.getElementById('edit_shelf_id').value = shelfId;
            document.getElementById('edit_product_id').value = productId;
            // You might want to fetch current quantity and location here via AJAX
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html> 