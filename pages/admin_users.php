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
    "SELECT u.id, u.first_name, u.last_name, u.email, u.role, COALESCE(p.name, 'Free') AS plan_name
     FROM users u
     LEFT JOIN user_subscriptions us ON us.user_id = u.id
     LEFT JOIN plans p ON p.id = us.plan_id
     ORDER BY u.role, u.first_name",
    [], 15
);
$users = $pagination['items'];

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/pagination.tpl.php';

drawAdminUsersPage($users, $ALLOWED_ROLES, $pagination['current_page'], $pagination['total_pages']);
require_once __DIR__ . '/../templates/footer.php';
?>