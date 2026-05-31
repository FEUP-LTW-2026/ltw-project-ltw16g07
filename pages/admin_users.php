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

$pagination = paginate($db,
    "SELECT COUNT(*) FROM users",
    "SELECT id, first_name, last_name, email, role FROM users ORDER BY role, first_name",
    [], 15
);
$users = $pagination['items'];

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/pagination.tpl.php';

drawAdminUsersPage($users, $ALLOWED_ROLES, $pagination['current_page'], $pagination['total_pages']);
require_once __DIR__ . '/../templates/footer.php';
?>