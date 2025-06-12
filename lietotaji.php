<?php
require_once 'config.php';
require_once 'sidebar.php';


if (isset($_POST['delete_user']) && isset($_POST['user_id']) && $_SESSION['role'] === 0) {
    $delete_id = (int)$_POST['user_id'];
    if ($delete_id !== $_SESSION['user_id']) { // Neļaut dzēst sevi
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_id]);
    }
}


$add_message = '';
if (isset($_POST['add_user']) && $_SESSION['role'] === 0) {
    $username = trim($_POST['new_username'] ?? '');
    $email = trim($_POST['new_email'] ?? '');
    $password = $_POST['new_password'] ?? '';
    $role = (int)($_POST['new_role'] ?? 1);
    if ($username === '' || $email === '' || $password === '') {
        $add_message = '<div class="error">Aizpildi visus laukus!</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $add_message = '<div class="error">Nederīgs e-pasts!</div>';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $add_message = '<div class="error">Lietotājvārdam jābūt 3-20 simboli!</div>';
    } elseif (strlen($password) < 6) {
        $add_message = '<div class="error">Parolei jābūt vismaz 6 simboli!</div>';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $add_message = '<div class="error">Lietotājs ar šādu e-pastu vai lietotājvārdu jau eksistē!</div>';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            if ($stmt->execute([$username, $email, $hashed, $role])) {
                $add_message = '<div class="success">Lietotājs pievienots!</div>';
            } else {
                $add_message = '<div class="error">Kļūda pievienojot lietotāju!</div>';
            }
        }
    }
}

echo '<div class="main-content">';
if (isset($_SESSION['role']) && $_SESSION['role'] === 0):
    echo '<div class="admin-panel">';
    echo '<h2>Lietotāji</h2>';

    echo '<h3>Pievienot jaunu lietotāju</h3>';
    if ($add_message) echo $add_message;
    echo '<form method="post" class="user-form">';
    echo '<div class="form-group"><label>Lietotājvārds</label><input type="text" name="new_username" class="form-control"></div>';
    echo '<div class="form-group"><label>E-pasts</label><input type="email" name="new_email" class="form-control"></div>';
    echo '<div class="form-group"><label>Parole</label><input type="password" name="new_password" class="form-control"></div>';
    echo '<div class="form-group"><label>Loma</label><select name="new_role" class="form-control"><option value="0">Administrators</option><option value="1">Noliktavas darbinieks</option><option value="2">Plauktu kārtotājs</option></select></div>';
    echo '<button type="submit" name="add_user" class="btn btn-primary">Pievienot lietotāju</button>';
    echo '</form>';

    try {
        $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users");
        $users = $stmt->fetchAll();
        if ($users) {
            echo '<table class="products-table" style="margin-top:32px;">';
            echo '<tr><th>ID</th><th>Lietotājvārds</th><th>E-pasts</th><th>Loma</th><th>Izveidots</th><th>Darbības</th></tr>';
            foreach ($users as $user) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($user['id']) . '</td>';
                echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                echo '<td class="editable-role" data-user-id="' . htmlspecialchars($user['id']) . '" data-current-role="' . htmlspecialchars($user['role']) . '"><span class="role-text">';
                switch ($user['role']) {
                    case 0: echo 'Administrators'; break;
                    case 1: echo 'Noliktavas darbinieks'; break;
                    case 2: echo 'Plauktu kārtotājs'; break;
                    default: echo 'Nezināma loma';
                }
                echo '</span></td>';
                echo '<td>' . htmlspecialchars($user['created_at']) . '</td>';
                echo '<td>';
                echo '<form method="post" style="display:inline-block; margin-right:4px;">';
                echo '<input type="hidden" name="user_id" value="' . $user['id'] . '">';
                echo '<button type="submit" name="delete_user" class="btn btn-danger" onclick="return confirm(\'Vai tiešām dzēst šo lietotāju?\')">Dzēst</button>';
                echo '</form>';
                echo '<a href="edit_user.php?id=' . $user['id'] . '" class="btn btn-primary" style="margin-right:4px;">Rediģēt</a>';
                echo '<a href="profile.php?id=' . $user['id'] . '" class="btn" style="background:#8D6E63; color:#fff;">Profils</a>';
                echo '</td>';
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