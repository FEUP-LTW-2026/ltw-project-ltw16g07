<?php
session_start();
require_once __DIR__ . '/../config/database.php';
// Importa o novo template do painel principal
require_once __DIR__ . '/../templates/dashboard.tpl.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['user_role'] ?? 'member';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_progress'])) {
    validate_csrf();

    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT);
    $body_fat = filter_input(INPUT_POST, 'body_fat', FILTER_VALIDATE_FLOAT);
    $body_fat = $body_fat > 0 ? $body_fat : null;

    if ($weight && $weight > 0) {
        try {
            $stmt = $db->prepare("INSERT INTO member_progress (user_id, weight, body_fat) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $weight, $body_fat]);
            $_SESSION['flash_success'] = 'Progress tracked successfully!';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Failed to record progress: ' . $e->getMessage();
        }
    } else {
        $_SESSION['flash_error'] = 'Please enter a valid weight.';
    }
    header('Location: dashboard.php');
    exit;
}

$current_page = 'home';
$extra_css = '../css/dashboard.css';
$page_title = 'Dashboard';

$name    = $_SESSION['user_name'] ?? 'User';

// Inicialização dos arrays para evitar erros de variáveis indefinidas no template
$stats            = [];
$my_classes       = [];
$latest_progress  = null;
$progress_history = [];
$recent_users     = [];

/* ── MEMBER stats ─────────────────────────────────────── */
if ($role === 'member') {
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats['my_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $db->query("SELECT COUNT(*) as total FROM classes");
    $stats['available_classes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $db->query("SELECT COUNT(*) as total FROM trainers");
    $stats['trainers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $db->prepare("
        SELECT c.*, t.name as trainer_name, b.id as booking_id
        FROM bookings b
        JOIN classes c ON b.class_id = c.id
        JOIN trainers t ON c.trainer_id = t.id
        WHERE b.user_id = ?
        ORDER BY
            CASE c.day_of_week
                WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2
                WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4
                WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6
                WHEN 'Sunday' THEN 7
            END, c.time_start
        LIMIT 4
    ");
    $stmt->execute([$user_id]);
    $my_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recolha de dados de progresso
    $stmt = $db->prepare("SELECT * FROM member_progress WHERE user_id = ? ORDER BY recorded_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $latest_progress = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    $stmt = $db->prepare("SELECT * FROM member_progress WHERE user_id = ? ORDER BY recorded_at DESC LIMIT 3");
    $stmt->execute([$user_id]);
    $progress_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ── TRAINER stats ────────────────────────────────────── */
if ($role === 'trainer') {
    $stmt = $db->prepare("SELECT * FROM trainers WHERE name = ?");
    $stmt->execute([$name]);
    $trainer = $stmt->fetch(PDO::FETCH_ASSOC);
    $trainer_id = $trainer ? $trainer['id'] : null;

    $stats['my_classes'] = 0;
    $stats['enrolled']   = 0;
    $stats['rating']     = $trainer['rating'] ?? '–';
    $stats['reviews']    = $trainer['reviews_count'] ?? 0;

    if ($trainer_id) {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM classes WHERE trainer_id = ?");
        $stmt->execute([$trainer_id]);
        $stats['my_classes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $stmt = $db->prepare("SELECT SUM(booked_spots) as total FROM classes WHERE trainer_id = ?");
        $stmt->execute([$trainer_id]);
        $stats['enrolled'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $stmt = $db->prepare("
            SELECT * FROM classes WHERE trainer_id = ?
            ORDER BY
                CASE day_of_week
                    WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2
                    WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4
                    WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6
                    WHEN 'Sunday' THEN 7
                END, time_start
            LIMIT 4
        ");
        $stmt->execute([$trainer_id]);
        $my_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/* ── ADMIN stats ──────────────────────────────────────── */
if ($role === 'admin') {
    $stats['users']     = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['classes']   = $db->query("SELECT COUNT(*) FROM classes")->fetchColumn();
    $stats['equipment'] = $db->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
    $stats['bookings']  = $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

    $recent_users = $db->query("
        SELECT first_name, last_name, role, created_at
        FROM users ORDER BY created_at DESC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
}

// Início da montagem estrutural da página
require_once __DIR__ . '/../templates/header.php';

// Renderização do conteúdo dinâmico
drawDashboardPage($name, $role, $stats, $my_classes, $latest_progress, $progress_history, $recent_users);

require_once __DIR__ . '/../templates/footer.php';
?>