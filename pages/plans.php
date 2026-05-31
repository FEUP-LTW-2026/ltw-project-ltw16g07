<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/plans.tpl.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SESSION['user_role'] !== 'member') {
    header('Location: ../pages/dashboard.php');
    exit;
}

$current_page = 'plans';
$page_title = 'Membership Plans';
$extra_css = '../css/style.css';

$user_id = $_SESSION['user_id'];
$current_plan = get_user_plan($db, $user_id);

$plans = $db->query("SELECT * FROM plans ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);

$user_bookings_count = 0;
if ($current_plan['max_bookings'] !== null) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_bookings_count = (int) $stmt->fetchColumn();
}

drawPlansPage($plans, $current_plan, $user_bookings_count);
require_once __DIR__ . '/../templates/footer.php';
