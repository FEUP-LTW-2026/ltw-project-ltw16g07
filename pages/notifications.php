<?php
session_start();
require_once __DIR__ . '/../config/database.php';
// Importa o novo template criado
require_once __DIR__ . '/../templates/notifications.tpl.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$current_page = 'notifications';
$page_title   = 'Notifications';
$user_id      = $_SESSION['user_id'];
$extra_css    = '../css/notifications.css';

// Marcar todas como lidas ao abrir a página
$db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")
   ->execute([$user_id]);

// Procurar notificações
$pagination = paginate($db,
    "SELECT COUNT(*) FROM notifications WHERE user_id = ?",
    "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC",
    [$user_id], 10
);
$notifications = $pagination['items'];

$icons = [
    'booking' => '📅',
    'review'  => '⭐',
    'cancel'  => '❌',
    'general' => '🔔',
];

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/pagination.tpl.php';

drawNotificationsList($notifications, $icons, $pagination['current_page'], $pagination['total_pages']);

require_once __DIR__ . '/../templates/footer.php'; 
?>