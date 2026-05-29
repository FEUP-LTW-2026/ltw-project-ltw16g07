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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf(); 

    if (isset($_POST['delete_notification'])) {
        $db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?")
           ->execute([(int) $_POST['notif_id'], $user_id]);
        header('Location: notifications.php');
        exit;
    }

    if (isset($_POST['clear_all'])) {
        $db->prepare("DELETE FROM notifications WHERE user_id = ?")
           ->execute([$user_id]);
        header('Location: notifications.php');
        exit;
    }
}

// Procurar notificações
$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$icons = [
    'booking' => '📅',
    'review'  => '⭐',
    'cancel'  => '❌',
    'general' => '🔔',
];

// Renderiza a estrutura da página
require_once __DIR__ . '/../templates/header.php';

// Chama a função do template passando os dados reais
drawNotificationsList($notifications, $icons);

require_once __DIR__ . '/../templates/footer.php'; 
?>