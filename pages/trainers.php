<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../templates/trainers.tpl.php';

$current_page = 'trainers';
$extra_css = '../css/profile-trainers.css';
$extra_js = '../javascript/trainers.js';
$page_title = 'Trainers';

$trainer_id = $_GET['id'] ?? null;

if ($trainer_id) {
    // 1. Vista de perfil único
    $stmt = $db->prepare("
        SELECT t.*, u.profile_pic 
        FROM trainers t 
        LEFT JOIN users u ON (u.first_name || ' ' || u.last_name) = t.name 
        WHERE t.id = ?
    ");
    $stmt->execute([$trainer_id]);
    $trainer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trainer) {
        http_response_code(404);
        die("Trainer not found.");
    }

    $stmt = $db->prepare("
        SELECT * FROM classes WHERE trainer_id = ? 
        ORDER BY 
            CASE day_of_week 
                WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2 
                WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4 
                WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6 
                WHEN 'Sunday' THEN 7 
            END, time_start
    ");
    $stmt->execute([$trainer_id]);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT * FROM reviews WHERE trainer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$trainer_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Renderiza a página
    require_once __DIR__ . '/../templates/header.php';
    drawTrainerProfile($trainer, $classes, $reviews);
    require_once __DIR__ . '/../templates/footer.php';
 
} else {
    // 2. Vista geral - Puxa rigorosamente todos os registos ordenados por nome
    $all_trainers = $db->query("
        SELECT t.*, u.profile_pic 
        FROM trainers t 
        LEFT JOIN users u ON (u.first_name || ' ' || u.last_name) = t.name \n        ORDER BY t.name
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Renderiza a listagem completa
    require_once __DIR__ . '/../templates/header.php';
    drawTrainersGrid($all_trainers);
    require_once __DIR__ . '/../templates/footer.php';
}
?>