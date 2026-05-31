<?php
session_start();

// 1. INICIALIZAÇÃO DA BASE DE DADOS (Imprescindível para o header.php não falhar)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../templates/profile.tpl.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php'); 
    exit;
}

// 2. CONFIGURAÇÕES VISUAIS DO CABEÇALHO
$current_page = 'profile';
$extra_css = '../css/profile.css';
$extra_js = ['https://cdn.jsdelivr.net/npm/chart.js', '../javascript/profile.js'];
$page_title = 'My Profile';
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// 3. QUERIES DE RENDERIZAÇÃO GRÁFICA
$stmt = $db->prepare("SELECT * FROM member_progress WHERE user_id = ? ORDER BY recorded_at DESC");
$stmt->execute([$user_id]);
$metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chart_data = array_reverse($metrics);
$labels = []; $weights = []; $fats = [];
foreach ($chart_data as $m) {
    $labels[]  = date('M j', strtotime($m['recorded_at']));
    $weights[] = $m['weight'];
    $fats[]    = $m['body_fat'];
}
$labels_json = json_encode($labels);
$weight_json = json_encode($weights);
$fat_json    = json_encode($fats);

$stmt = $db->prepare("SELECT pt.*, t.name AS trainer_name FROM pt_bookings pt JOIN trainers t ON pt.trainer_id = t.id WHERE pt.user_id = ? ORDER BY pt.booking_date ASC");
$stmt->execute([$user_id]);
$pt_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT b.*, c.title, c.day_of_week, c.time_start FROM bookings b JOIN classes c ON b.class_id = c.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
$stmt->execute([$user_id]);
$classes_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);


// 5. COMPOSIÇÃO DA ESTRUTURA VISUAL
require_once __DIR__ . '/../templates/header.php';

drawUserProfile($user, $metrics, $pt_bookings, $classes_bookings);
require_once __DIR__ . '/../templates/footer.php';
?>