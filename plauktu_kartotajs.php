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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_shelf'])) {
        $shelf_identifier = trim($_POST['new_shelf_identifier'] ?? '');

        if (empty($shelf_identifier)) {
            $message = '<div class="error">Plaukta identifikators nevar būt tukšs.</div>';
        } else {
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
        }
    } elseif (isset($_POST['update_shelf'])) {
        $shelf_id = $_POST['shelf_id'];
        $shelf_identifier = trim($_POST['shelf_identifier'] ?? '');

        if (empty($shelf_identifier)) {
            $message = '<div class="error">Plaukta identifikators nevar būt tukšs.</div>';
        } else {
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
        }
    } elseif (isset($_POST['add_product_to_shelf'])) {
        $shelf_id = filter_var($_POST['shelf_id'] ?? '', FILTER_VALIDATE_INT);
        $product_id = filter_var($_POST['product_id'] ?? '', FILTER_VALIDATE_INT);
        $quantity = filter_var($_POST['quantity'] ?? '', FILTER_VALIDATE_INT);

        if (!$shelf_id || !$product_id || !$quantity || $quantity <= 0) {
            $message = '<div class="error">Lūdzu, ievadiet derīgu plauktu, preci un daudzumu.</div>';
        } else {
            try {
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
                        $stmt2 = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                        $stmt2->execute([$quantity, $product_id]);
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
    } elseif (isset($_POST['delete_whole_shelf'])) {
        $shelf_id = (int)$_POST['shelf_id'];
        if (!$shelf_id) {
            $message = '<div class="error">Nederīgs plaukta ID.</div>';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM shelf_products WHERE shelf_id = ?");
                $stmt->execute([$shelf_id]);
                $stmt = $pdo->prepare("DELETE FROM shelf_inventory WHERE id = ?");
                if ($stmt->execute([$shelf_id])) {
                    $message = '<div class="success">Plaukts un visas tajā esošās preces veiksmīgi dzēstas!</div>';
                } else {
                    $message = '<div class="error">Kļūda dzēšot plauktu.</div>';
                }
            } catch (PDOException $e) {
                $message = '<div class="error">Kļūda dzēšot plauktu: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
    } elseif (isset($_POST['delete_quantity_from_shelf'])) {
        $shelf_id = filter_var($_POST['shelf_id'] ?? '', FILTER_VALIDATE_INT);
        $product_id = filter_var($_POST['product_id_for_delete'] ?? '', FILTER_VALIDATE_INT);
        $delete_quantity = filter_var($_POST['delete_quantity'] ?? '', FILTER_VALIDATE_INT);

        if (!$shelf_id || !$product_id || !$delete_quantity || $delete_quantity <= 0) {
            $message = '<div class="error">Lūdzu, ievadiet derīgu plauktu, preci un daudzumu.</div>';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT quantity FROM shelf_products WHERE shelf_id = ? AND product_id = ?");
                $stmt->execute([$shelf_id, $product_id]);
                $row = $stmt->fetch();
                if ($row && $row['quantity'] >= $delete_quantity) {
                    if ($row['quantity'] == $delete_quantity) {
                        $stmt = $pdo->prepare("DELETE FROM shelf_products WHERE shelf_id = ? AND product_id = ?");
                        $stmt->execute([$shelf_id, $product_id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE shelf_products SET quantity = quantity - ? WHERE shelf_id = ? AND product_id = ?");
                        $stmt->execute([$delete_quantity, $shelf_id, $product_id]);
                    }
                    $message = '<div class="success">Norādītais preču daudzums veiksmīgi dzēsts no plaukta!</div>';
                } else {
                    $message = '<div class="error">Nav tik daudz preču šajā plauktā.</div>';
                }
            } catch (PDOException $e) {
                $message = '<div class="error">Kļūda dzēšot preces no plaukta: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
    }
}

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
                    <h3><a href="atskaites.php" style="color:inherit;text-decoration:underline;">Atskaišu Sagatavošana</a></h3>
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
                                    <button class="btn-delete" onclick="openDeleteModal(<?php echo htmlspecialchars(json_encode($item['id'])); ?>, '<?php echo htmlspecialchars($item['shelf_identifier']); ?>')">Dzēst</button>
                                </td>
                            </tr>
                            <?php } ?>
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
                                <input type="text" id="new_shelf_identifier" name="new_shelf_identifier">
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
                                <input type="text" id="shelf_identifier" name="shelf_identifier" value="<?php echo htmlspecialchars($_POST['shelf_identifier']); ?>">
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

    <div id="deleteModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeDeleteModal()">&times;</span>
            <h2>Dzēst plauktu</h2>
            <p id="modalShelfInfo"></p>
            <form method="POST" id="deleteShelfForm">
                <input type="hidden" name="shelf_id" id="delete_shelf_id">
                <button type="submit" name="delete_whole_shelf" class="btn-danger" style="margin-bottom:10px;width:100%;">Dzēst VISU plauktu</button>
            </form>
            <hr>
            <form method="POST" id="deleteProductFromShelfForm">
                <input type="hidden" name="shelf_id" id="delete_product_shelf_id">
                <div class="form-group">
                    <label for="product_id_for_delete">Izvēlies preci no plaukta:</label>
                    <select name="product_id_for_delete" id="product_id_for_delete" required>
                        <option value="">Izvēlies preci</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="delete_quantity">Dzēst preču daudzumu no plaukta:</label>
                    <input type="number" name="delete_quantity" id="delete_quantity" min="1" required style="width:80px;">
                </div>
                <button type="submit" name="delete_quantity_from_shelf" class="btn-danger">Dzēst norādīto daudzumu</button>
            </form>
        </div>
    </div>

    <script>
    function openDeleteModal(shelfId, shelfIdentifier) {
        document.getElementById('deleteModal').style.display = 'block';
        document.getElementById('delete_shelf_id').value = shelfId;
        document.getElementById('delete_product_shelf_id').value = shelfId;
        document.getElementById('modalShelfInfo').innerText = 'Plaukts: ' + shelfIdentifier;
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }
    window.onclick = function(event) {
        var modal = document.getElementById('deleteModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    </script>
</body>
</html> 