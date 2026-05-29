<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
// Importação obrigatória do novo template administrativo
require_once __DIR__ . '/../templates/admin.tpl.php';

// Proteção de rota para administradores
require_admin();

$current_page = 'admin';
$extra_css    = '../css/admin.css';
$page_title   = 'Admin Dashboard';

// Recolha e processamento estatístico dos dados
$stats = [];
$stats['users']     = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['admins']    = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$stats['trainers']  = $db->query("SELECT COUNT(*) FROM trainers")->fetchColumn();
$stats['classes']   = $db->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$stats['equipment'] = $db->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
$stats['bookings']  = $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

// --- SEQUÊNCIA DE RENDERIZAÇÃO CORRETA ---
require_once __DIR__ . '/../templates/header.php';

// Invocação da função com as variáveis necessárias
drawAdminDashboardPage($stats);

require_once __DIR__ . '/../templates/footer.php';
?>