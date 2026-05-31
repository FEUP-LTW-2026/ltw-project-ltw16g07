<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../templates/admin_equipment.tpl.php';

require_admin();

$current_page = 'admin';
$extra_css    = '../css/admin.css';
$extra_js     = '../javascript/admin_equipment.js';
$page_title   = 'Manage Equipment';

$pagination = paginate($db,
    "SELECT COUNT(*) FROM equipment",
    "SELECT * FROM equipment ORDER BY category, name",
    [], 15
);
$equipment = $pagination['items'];

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/pagination.tpl.php';

drawAdminEquipmentPage($equipment, $pagination['current_page'], $pagination['total_pages']);
require_once __DIR__ . '/../templates/footer.php';
?>