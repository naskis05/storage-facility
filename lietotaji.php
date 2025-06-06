<?php
require_once 'config.php';
require_once 'sidebar.php';

echo '<div class="main-content">';
if (isset($_SESSION['role']) && $_SESSION['role'] === 0):
    echo '<div class="admin-panel" style="margin-top: 32px; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">';
    echo '<h2>Admin CRUD Panel</h2>';
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
echo '</div>';
?> 

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.editable-role .role-text').forEach(function(span) {
        span.addEventListener('click', function() {
            var td = this.parentElement;
            var userId = td.getAttribute('data-user-id');
            var currentRole = td.getAttribute('data-current-role');
            
            this.style.display = 'none';

            var select = document.createElement('select');
            select.innerHTML = '';
            select.innerHTML += '<option value="0">Administrators</option>';
            select.innerHTML += '<option value="1">Noliktavas darbinieks</option>';
            select.innerHTML += '<option value="2">Plauktu kārtotājs</option>';

            select.value = currentRole;


            select.addEventListener('change', function() {
                var newRole = this.value;
                var selectElement = this;

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
                         switch (parseInt(newRole)) {
                            case 0: roleTextSpan.textContent = 'Administrators'; break;
                            case 1: roleTextSpan.textContent = 'Noliktavas darbinieks'; break;
                            case 2: roleTextSpan.textContent = 'Plauktu kārtotājs'; break;
                            default: roleTextSpan.textContent = 'Nezināma loma';
                        }
                        td.setAttribute('data-current-role', newRole);
                        console.log(data.message);
                    } else {
                        console.error('Error updating role:', data.message);
                         switch (parseInt(currentRole)) {
                            case 0: roleTextSpan.textContent = 'Administrators'; break;
                            case 1: roleTextSpan.textContent = 'Noliktavas darbinieks'; break;
                            case 2: roleTextSpan.textContent = 'Plauktu kārtotājs'; break;
                            default: roleTextSpan.textContent = 'Nezināma loma';
                        }
                         alert('Kļūda atjaunojot lomu: ' + data.message);   
                    }
                    selectElement.remove();
                    roleTextSpan.style.display = 'inline';
                })
                .catch(error => {
                    console.error('Error in AJAX request:', error);
                     var roleTextSpan = td.querySelector('.role-text');
                     switch (parseInt(currentRole)) {
                        case 0: roleTextSpan.textContent = 'Administrators'; break;
                        case 1: roleTextSpan.textContent = 'Noliktavas darbinieks'; break;
                        case 2: roleTextSpan.textContent = 'Plauktu kārtotājs'; break;
                        default: roleTextSpan.textContent = 'Nezināma loma';
                    }
                    selectElement.remove();
                    roleTextSpan.style.display = 'inline';
                    alert('Radās tīkla kļūda, mēģiniet vēlreiz.');
                });

            });

            select.addEventListener('blur', function() {
                this.remove();
                td.querySelector('.role-text').style.display = 'inline';
            });
            
            td.appendChild(select);
            select.focus();
        });
    });
});
</script> 