<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../templates/admin_classes.tpl.php';

require_admin();

$current_page = 'admin';
$extra_css    = '../css/admin.css';
$extra_js     = '../javascript/admin_classes.js';
$page_title   = 'Manage Classes';

$trainers     = $db->query("SELECT id, name FROM trainers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$DAYS_OF_WEEK = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$CLASS_TYPES  = ['Yoga', 'HIIT', 'Pilates', 'Spinning', 'Boxing'];

$pagination = paginate($db,
    "SELECT COUNT(*) FROM classes",
    "SELECT c.*, t.name AS trainer_name FROM classes c JOIN trainers t ON c.trainer_id = t.id
     ORDER BY CASE c.day_of_week
         WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2
         WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4
         WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6
         WHEN 'Sunday' THEN 7
     END, c.time_start",
    [], 15
);
$classes = $pagination['items'];

require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/pagination.tpl.php';

drawAdminClassesPage($classes, $trainers, $DAYS_OF_WEEK, $CLASS_TYPES, $pagination['current_page'], $pagination['total_pages']);

require_once __DIR__ . '/../templates/footer.php';
?>