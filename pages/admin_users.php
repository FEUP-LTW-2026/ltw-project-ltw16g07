<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../templates/admin_users.tpl.php';

require_admin();

$current_page = 'admin';
$extra_css    = '../css/admin.css';
$extra_js     = '../javascript/admin_users.js';
$page_title   = 'Manage Users';

$ALLOWED_ROLES = ['member', 'trainer', 'admin'];

$users = $db->query("SELECT id, first_name, last_name, email, role FROM users ORDER BY role, first_name")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../templates/header.php';

// Invocação da renderização isolada do template
drawAdminUsersPage($users, $ALLOWED_ROLES);
require_once __DIR__ . '/../templates/footer.php';
?>