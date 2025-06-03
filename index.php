<?php require_once 'config.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My PHP Website</title>
</head>
<body>
    <h1>Welcome to My PHP Website</h1>

    <p>
        <?php
        echo "Hello, world! This is your default PHP site.";
        ?>
    </p>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> My PHP Site</p>
    </footer>
</body>
</html>
