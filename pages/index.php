<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../templates/index.tpl.php';

// Se o utilizador já estiver autenticado, vai direto para o dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}

$page_title = 'FitHub';

// Executa as consultas para alimentar a página
$stmt = $db->query("SELECT id, name, specialties, rating, photo_initials FROM trainers WHERE id <= 4 ORDER BY rating DESC");
$featured_trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT DISTINCT type FROM classes ORDER BY type");
$class_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stats = [];
$stmt = $db->query("SELECT COUNT(*) as total FROM classes");
$stats['classes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM trainers");
$stats['trainers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM equipment");
$stats['equipment'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT ROUND(AVG(rating), 1) as avg FROM trainers");
$stats['avg_rating'] = $stmt->fetch(PDO::FETCH_ASSOC)['avg'];

// Função utilitária contra XSS
if (!function_exists('h')) {
    function h($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

// Renderiza a página chamando o template
drawHomePage($featured_trainers, $class_types, $stats, $page_title);
?>

<script src="../javascript/index.js" defer></script>