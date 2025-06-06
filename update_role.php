<?php
require_once 'config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['userId'] ?? null;
    $newRole = $_POST['newRole'] ?? null;


    if ($userId !== null && $newRole !== null && is_numeric($userId) && is_numeric($newRole) && in_array($newRole, [0, 1, 2])) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");

            if ($stmt->execute([$newRole, $userId])) {
                echo json_encode(['status' => 'success', 'message' => 'User role updated successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update user role.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?> 