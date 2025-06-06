<?php
require_once 'config.php';
require_once 'sidebar.php';

// // Main content wrapper
// echo '<div class="main-content">';
// // Admin CRUD panel for administrators only
// if (isset($_SESSION['role']) && $_SESSION['role'] === 0):
//     echo '<div class="admin-panel" style="margin-top: 32px; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">';
//     echo '<h2>Admin CRUD Panel</h2>';
//     // Fetch all users
//     try {
//         $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users");
//         $users = $stmt->fetchAll();
//         if ($users) {
//             echo '<table border="1" cellpadding="8" style="border-collapse:collapse; width:100%; margin-top:16px;">';
//             echo '<tr><th>ID</th><th>Lietotājvārds</th><th>E-pasts</th><th>Loma</th><th>Izveidots</th></tr>';
//             foreach ($users as $user) {
//                 echo '<tr>';
//                 echo '<td>' . htmlspecialchars($user['id']) . '</td>';
//                 echo '<td>' . htmlspecialchars($user['username']) . '</td>';
//                 echo '<td>' . htmlspecialchars($user['email']) . '</td>';
//                 echo '<td>';
//                 switch ($user['role']) {
//                     case 0: echo 'Administrators'; break;
//                     case 1: echo 'Noliktavas darbinieks'; break;
//                     case 2: echo 'Plauktu kārtotājs'; break;
//                     default: echo 'Nezināma loma';
//                 }
//                 echo '</td>';
//                 echo '<td>' . htmlspecialchars($user['created_at']) . '</td>';
//                 echo '</tr>';
//             }
//             echo '</table>';
//         } else {
//             echo '<p>Nav lietotāju datubāzē.</p>';
//         }
//     } catch (PDOException $e) {
//         echo '<p style="color: red;">Kļūda iegūstot lietotājus: ' . htmlspecialchars($e->getMessage()) . '</p>';
//     }
//     echo '<p style="margin-top:16px;">Šī sadaļa ir redzama tikai administratoriem.</p>';
//     echo '</div>';
// endif;
// echo '</div>'; // Close main-content
// ?>