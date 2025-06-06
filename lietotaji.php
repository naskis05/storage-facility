<?php
require_once 'config.php';
require_once 'sidebar.php';

// Main content wrapper
echo '<div class="main-content">';
// Admin CRUD panel for administrators only
if (isset($_SESSION['role']) && $_SESSION['role'] === 0):
    echo '<div class="admin-panel" style="margin-top: 32px; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">';
    echo '<h2>Admin CRUD Panel</h2>';
    // Fetch all users
    try {
        $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users");
        $users = $stmt->fetchAll();
        if ($users) {
            echo '<table border="1" cellpadding="8" style="border-collapse:collapse; width:100%; margin-top:16px;">';
            echo '<tr><th>ID</th><th>Lietotājvārds</th><th>E-pasts</th><th>Loma</th><th>Izveidots</th></tr>';
            foreach ($users as $user) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($user['id']) . '</td>';
                echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                echo '<td class="editable-role" data-user-id="' . htmlspecialchars($user['id']) . '" data-current-role="' . htmlspecialchars($user['role']) . '">';
                echo '<span class="role-text">';
                switch ($user['role']) {
                    case 0: echo 'Administrators'; break;
                    case 1: echo 'Noliktavas darbinieks'; break;
                    case 2: echo 'Plauktu kārtotājs'; break;
                    default: echo 'Nezināma loma';
                }
                echo '</span></td>';
                echo '<td>' . htmlspecialchars($user['created_at']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>Nav lietotāju datubāzē.</p>';
        }
    } catch (PDOException $e) {
        echo '<p style="color: red;">Kļūda iegūstot lietotājus: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    echo '<p style="margin-top:16px;">Šī sadaļa ir redzama tikai administratoriem.</p>';
    echo '</div>';
endif;
echo '</div>'; // Close main-content
?> 

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.editable-role .role-text').forEach(function(span) {
        span.addEventListener('click', function() {
            var td = this.parentElement;
            var userId = td.getAttribute('data-user-id');
            var currentRole = td.getAttribute('data-current-role');
            
            // Hide text
            this.style.display = 'none';

            // Create select dropdown
            var select = document.createElement('select');
            select.innerHTML = ''; // Clear previous options if any
            select.innerHTML += '<option value="0">Administrators</option>';
            select.innerHTML += '<option value="1">Noliktavas darbinieks</option>';
            select.innerHTML += '<option value="2">Plauktu kārtotājs</option>';

            // Set current role as selected
            select.value = currentRole;

            // Add event listeners for change and blur
            select.addEventListener('change', function() {
                var newRole = this.value;
                var selectElement = this; // Keep reference to the select element

                // Send AJAX request to update the database
                fetch('update_role.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'userId=' + encodeURIComponent(userId) + '&newRole=' + encodeURIComponent(newRole)
                })
                .then(response => response.json())
                .then(data => {
                    var roleTextSpan = td.querySelector('.role-text');
                    if (data.status === 'success') {
                        // Update the text displayed on success
                         switch (parseInt(newRole)) {
                            case 0: roleTextSpan.textContent = 'Administrators'; break;
                            case 1: roleTextSpan.textContent = 'Noliktavas darbinieks'; break;
                            case 2: roleTextSpan.textContent = 'Plauktu kārtotājs'; break;
                            default: roleTextSpan.textContent = 'Nezināma loma';
                        }
                        td.setAttribute('data-current-role', newRole);
                        // Optionally display a temporary success message
                        console.log(data.message);
                    } else {
                        // Revert the displayed text and show an error message on failure
                        console.error('Error updating role:', data.message);
                         // Revert displayed text to the old value
                         switch (parseInt(currentRole)) {
                            case 0: roleTextSpan.textContent = 'Administrators'; break;
                            case 1: roleTextSpan.textContent = 'Noliktavas darbinieks'; break;
                            case 2: roleTextSpan.textContent = 'Plauktu kārtotājs'; break;
                            default: roleTextSpan.textContent = 'Nezināma loma';
                        }
                         // Optionally display a more visible error message to the user
                         alert('Kļūda atjaunojot lomu: ' + data.message); // Example using alert
                    }
                    selectElement.remove(); // Remove select after change
                    roleTextSpan.style.display = 'inline'; // Show text again
                })
                .catch(error => {
                    console.error('Error in AJAX request:', error);
                     var roleTextSpan = td.querySelector('.role-text');
                     // Revert displayed text on network error
                     switch (parseInt(currentRole)) {
                        case 0: roleTextSpan.textContent = 'Administrators'; break;
                        case 1: roleTextSpan.textContent = 'Noliktavas darbinieks'; break;
                        case 2: roleTextSpan.textContent = 'Plauktu kārtotājs'; break;
                        default: roleTextSpan.textContent = 'Nezināma loma';
                    }
                    selectElement.remove(); // Remove select after error
                    roleTextSpan.style.display = 'inline'; // Show text again
                    alert('Radās tīkla kļūda, mēģiniet vēlreiz.'); // Example using alert
                });

            });

            select.addEventListener('blur', function() {
                // If the dropdown loses focus without a change, revert
                this.remove();
                td.querySelector('.role-text').style.display = 'inline';
            });

            // Append select and focus it
            td.appendChild(select);
            select.focus();
        });
    });
});
</script> 