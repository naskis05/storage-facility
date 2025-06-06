<?php
require_once 'config.php';
require_once 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Preču pārvaldība</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
// Check if user is logged in and has appropriate role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !in_array($_SESSION['role'], [0, 1])) {
    header("Location: login.php");
    exit();
}

$message = '';

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$product_id])) {
            $message = '<div class="success">Prece veiksmīgi dzēsta!</div>';
        } else {
            $message = '<div class="error">Kļūda dzēšot preci.</div>';
        }
    } catch (PDOException $e) {
        $message = '<div class="error">Kļūda dzēšot preci: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $quantity = $_POST['quantity'] ?? '';
    $price = $_POST['price'] ?? '';

    // Backend validation
    if ($name === '' || $description === '' || $quantity === '' || $price === '') {
        $message = '<div class="error">Lūdzu, aizpildiet visus laukus ar derīgām vērtībām.</div>';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $message = '<div class="error">Preces nosaukumam jābūt 2-100 simbolus garam.</div>';
    } elseif (mb_strlen($description) < 5 || mb_strlen($description) > 500) {
        $message = '<div class="error">Aprakstam jābūt 5-500 simbolus garam.</div>';
    } elseif (!ctype_digit($quantity) || (int)$quantity <= 0) {
        $message = '<div class="error">Daudzumam jābūt pozitīvam veselam skaitlim.</div>';
    } elseif (!is_numeric($price) || (float)$price <= 0) {
        $message = '<div class="error">Cenai jābūt pozitīvam skaitlim.</div>';
    } elseif (preg_match('/^\d+(\.\d{1,2})?$/', $price) !== 1) {
        $message = '<div class="error">Cenai drīkst būt ne vairāk kā 2 cipari aiz komata.</div>';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, quantity, price, created_at) VALUES (?, ?, ?, ?, NOW())");
            if ($stmt->execute([$name, $description, (int)$quantity, (float)$price])) {
                $message = '<div class="success">Prece veiksmīgi pievienota!</div>';
            } else {
                $message = '<div class="error">Kļūda pievienojot preci.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="error">Kļūda pievienojot preci: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Main content wrapper
echo '<div class="main-content">';
echo '<div class="admin-panel">';
echo '<h2>Preču pārvaldība</h2>';

// Display message if any
if ($message) {
    echo $message;
}

// Add Product Form
echo '<div class="product-form">';
echo '<h3>Pievienot jaunu preci</h3>';
echo '<form method="post">';
echo '<div class="form-group">';
echo '<label for="name">Preces nosaukums</label>';
echo '<input type="text" id="name" name="name" class="form-control" required placeholder="Ievadiet preces nosaukumu">';
echo '</div>';

echo '<div class="form-group">';
echo '<label for="description">Apraksts</label>';
echo '<textarea id="description" name="description" class="form-control" required placeholder="Ievadiet preces aprakstu"></textarea>';
echo '</div>';

echo '<div class="form-group">';
echo '<label for="quantity">Daudzums</label>';
echo '<input type="number" id="quantity" name="quantity" class="form-control" required min="1" placeholder="Ievadiet daudzumu">';
echo '</div>';

echo '<div class="form-group">';
echo '<label for="price">Cena (EUR)</label>';
echo '<input type="number" id="price" name="price" class="form-control" required min="0.01" step="0.01" placeholder="Ievadiet cenu">';
echo '</div>';

echo '<button type="submit" name="add_product" class="btn btn-primary">Pievienot preci</button>';
echo '</form>';
echo '</div>';

// Products List
echo '<h3>Preču saraksts</h3>';
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll();
    
    if ($products) {
        echo '<table class="products-table">';
        echo '<tr>';
        echo '<th>Nosaukums</th>';
        echo '<th>Apraksts</th>';
        echo '<th>Daudzums</th>';
        echo '<th>Cena</th>';
        echo '<th class="actions-cell">Darbības</th>';
        echo '</tr>';
        
        foreach ($products as $product) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($product['name']) . '</td>';
            echo '<td>' . htmlspecialchars($product['description']) . '</td>';
            echo '<td class="quantity-cell">' . htmlspecialchars($product['quantity']) . '</td>';
            echo '<td class="price-cell">€' . number_format($product['price'], 2) . '</td>';
            echo '<td class="actions-cell">';
            echo '<form method="post" style="display: inline;">';
            echo '<input type="hidden" name="product_id" value="' . $product['id'] . '">';
            echo '<button type="submit" name="delete_product" onclick="return confirm(\'Vai tiešām vēlaties dzēst šo preci?\')" class="btn btn-danger">Dzēst</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>Nav pievienotu preču.</p>';
    }
} catch (PDOException $e) {
    echo '<p class="error">Kļūda iegūstot preces: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '</div>';
echo '</div>';
?>
</body>
</html> 