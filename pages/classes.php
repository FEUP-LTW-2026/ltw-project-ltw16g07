<?php
session_start();
require_once __DIR__ . '/../config/database.php';
// Importação do novo template visual das aulas
require_once __DIR__ . '/../templates/classes.tpl.php';

$current_page = 'classes';
$extra_css = '../css/classes.css';
$page_title = 'Classes';

// Se o utilizador atual for um treinador, recolhe o ID para validação própria
$logged_trainer_id = null;
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'trainer') {
    $stmt = $db->prepare("SELECT id FROM trainers WHERE name = ?");
    $stmt->execute([$_SESSION['user_name']]);
    $t = $stmt->fetch(PDO::FETCH_ASSOC);
    $logged_trainer_id = $t ? (int) $t['id'] : null;
}

// Filtros passados via parâmetros URL (GET)
$filter_type    = $_GET['type'] ?? 'All';
$filter_trainer = $_GET['trainer'] ?? '';
$filter_day     = $_GET['day'] ?? '';
$filter_time    = $_GET['time'] ?? '';

// Construção dinâmica da query SQL baseado nos filtros ativos
$sql = "
    SELECT c.*, t.name as trainer_name 
    FROM classes c 
    JOIN trainers t ON c.trainer_id = t.id 
    WHERE 1=1
";
$params = [];

if ($filter_type !== 'All' && $filter_type !== '') {
    $sql .= " AND c.type = ?";
    $params[] = $filter_type;
}

if ($filter_trainer !== '') {
    $sql .= " AND t.id = ?";
    $params[] = $filter_trainer;
}

if ($filter_day !== '') {
    $sql .= " AND c.day_of_week = ?";
    $params[] = $filter_day;
}

if ($filter_time !== '') {
    switch ($filter_time) {
        case 'morning':
            $sql .= " AND c.time_start < '12:00'";
            break;
        case 'afternoon':
            $sql .= " AND c.time_start >= '12:00' AND c.time_start < '17:00'";
            break;
        case 'evening':
            $sql .= " AND c.time_start >= '17:00'";
            break;
    }
}

$sql .= " ORDER BY 
    CASE c.day_of_week 
        WHEN 'Monday' THEN 1 
        WHEN 'Tuesday' THEN 2 
        WHEN 'Wednesday' THEN 3 
        WHEN 'Thursday' THEN 4 
        WHEN 'Friday' THEN 5 
        WHEN 'Saturday' THEN 6 
        WHEN 'Sunday' THEN 7 
    END, 
    c.time_start";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupamento estruturado das aulas organizadas por cada dia da semana
$days_order = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$classes_by_day = [];
foreach ($classes as $class) {
    $classes_by_day[$class['day_of_week']][] = $class;
}

// Alimentação das listas de seleção dinâmica nos filtros
$trainers = $db->query("SELECT id, name FROM trainers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$types    = $db->query("SELECT DISTINCT type FROM classes ORDER BY type")->fetchAll(PDO::FETCH_ASSOC);

// Início do output gráfico sequencial
require_once __DIR__ . '/../templates/header.php';

drawClassesPage(
    $classes, 
    $classes_by_day, 
    $days_order, 
    $trainers, 
    $types, 
    $filter_type, 
    $filter_trainer, 
    $filter_day, 
    $filter_time, 
    $logged_trainer_id
);

require_once __DIR__ . '/../templates/footer.php';
?>