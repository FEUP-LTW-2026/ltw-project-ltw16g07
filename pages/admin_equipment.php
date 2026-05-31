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

$equipment = $db->query("SELECT * FROM equipment ORDER BY category, name")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../templates/header.php';

// Renderiza a página recorrendo ao template purificado
drawAdminEquipmentPage($equipment);
require_once __DIR__ . '/../templates/footer.php';
?>