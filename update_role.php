<?php
require_once 'config.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the POST request
    $userId = $_POST['userId'] ?? null;
    $newRole = $_POST['newRole'] ?? null;

    // Validate the received data
    // You might want more robust validation depending on your needs
    if ($userId !== null && $newRole !== null && is_numeric($userId) && is_numeric($newRole) && in_array($newRole, [0, 1, 2])) {
        try {
            // Prepare the update statement
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");

            // Execute the statement
            if ($stmt->execute([$newRole, $userId])) {
                // Send a success response
                echo json_encode(['status' => 'success', 'message' => 'User role updated successfully.']);
            } else {
                // Send an error response if execution failed
                echo json_encode(['status' => 'error', 'message' => 'Failed to update user role.']);
            }
        } catch (PDOException $e) {
            // Send an error response if a database error occurred
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        // Send an error response for invalid input
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
    }
} else {
    // Send an error response for non-POST requests
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?> 