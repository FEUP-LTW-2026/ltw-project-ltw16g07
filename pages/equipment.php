<?php
session_start();
require_once __DIR__ . '/../config/database.php';
// Importa o template limpo
require_once __DIR__ . '/../templates/equipment.tpl.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

$current_page = 'equipment';
$page_title = 'Equipment';
$extra_css = '../css/equipment.css';
$extra_js = '../javascript/equipment.js';

// Lógica de recolha de dados
$pagination = paginate($db, 
    "SELECT COUNT(*) FROM equipment",
    "SELECT * FROM equipment ORDER BY category, name",
    [], 10
);
$equipment  = $pagination['items'];
$categories = $db->query("SELECT DISTINCT category FROM equipment ORDER BY category")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/pagination.tpl.php';

drawEquipmentPage($equipment, $categories, $pagination['current_page'], $pagination['total_pages']);

require_once __DIR__ . '/../templates/footer.php';
?>