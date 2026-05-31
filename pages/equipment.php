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
$equipment = $db->query("SELECT * FROM equipment ORDER BY category, name")->fetchAll(PDO::FETCH_ASSOC);
$categories = $db->query("SELECT DISTINCT category FROM equipment ORDER BY category")->fetchAll(PDO::FETCH_ASSOC);

// Renderização estrutural (Header, Template Visual e Footer)
require_once __DIR__ . '/../templates/header.php';

drawEquipmentPage($equipment, $categories);

require_once __DIR__ . '/../templates/footer.php';
?>