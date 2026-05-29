<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../templates/admin_classes.tpl.php';

require_admin();

$current_page = 'admin';
$extra_css    = '../css/admin.css';
$page_title   = 'Manage Classes';

$trainers     = $db->query("SELECT id, name FROM trainers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$DAYS_OF_WEEK = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$CLASS_TYPES  = ['Yoga', 'HIIT', 'Pilates', 'Spinning', 'Boxing'];

function gather_class_data(): ?array
{
    $title      = trim($_POST['title'] ?? '');
    $day        = $_POST['day_of_week'] ?? '';
    $start      = $_POST['time_start'] ?? '';
    $end        = $_POST['time_end'] ?? '';
    $location   = trim($_POST['location'] ?? '');
    $trainer_id = (int) ($_POST['trainer_id'] ?? 0);
    $spots      = (int) ($_POST['total_spots'] ?? 0);
    $type       = trim($_POST['type'] ?? '');

    return ($title && $day && $start && $end && $location && $trainer_id && $spots > 0 && $type)
        ? [$title, $day, $start, $end, $location, $trainer_id, $spots, $type]
        : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    if (isset($_POST['create_class'])) {
        $data = gather_class_data();
        if ($data) {
            [$title, $day, $start, $end, $location, $trainer_id, $spots, $type] = $data;
            $stmt = $db->prepare(
                "INSERT INTO classes (title, day_of_week, time_start, time_end, location, trainer_id, total_spots, type)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$title, $day, $start, $end, $location, $trainer_id, $spots, $type]);
            $_SESSION['flash_success'] = 'Class created successfully.';
        } else {
            $_SESSION['flash_error'] = 'Please fill in all fields.';
        }
    }

    if (isset($_POST['edit_class'])) {
        $class_id = (int) ($_POST['class_id'] ?? 0);
        $data     = gather_class_data();
        if ($class_id && $data) {
            [$title, $day, $start, $end, $location, $trainer_id, $spots, $type] = $data;
            $stmt = $db->prepare(
                "UPDATE classes SET title=?, day_of_week=?, time_start=?, time_end=?,
                    location=?, trainer_id=?, total_spots=?, type=? WHERE id=?"
            );
            $stmt->execute([$title, $day, $start, $end, $location, $trainer_id, $spots, $type, $class_id]);
            $_SESSION['flash_success'] = 'Class updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Please fill in all fields.';
        }
    }

    if (isset($_POST['delete_class'])) {
        $class_id = (int) ($_POST['class_id'] ?? 0);
        if ($class_id) {
            try {
                $db->beginTransaction();
                $db->prepare("DELETE FROM bookings WHERE class_id = ?")->execute([$class_id]);
                $db->prepare("DELETE FROM classes   WHERE id       = ?")->execute([$class_id]);
                $db->commit();
                $_SESSION['flash_success'] = 'Class deleted successfully.';
            } catch (Exception $e) {
                $db->rollBack();
                $_SESSION['flash_error'] = 'Something went wrong while deleting the class.';
            }
        }
    }

    header('Location: admin_classes.php');
    exit;
}

$stmt = $db->query("
    SELECT c.*, t.name AS trainer_name
    FROM classes c
    JOIN trainers t ON c.trainer_id = t.id
    ORDER BY
        CASE c.day_of_week
            WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2
            WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4
            WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6
            WHEN 'Sunday' THEN 7
        END, c.time_start
");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../templates/header.php';

drawAdminClassesPage($classes, $trainers, $DAYS_OF_WEEK, $CLASS_TYPES);

require_once __DIR__ . '/../templates/footer.php';
?>

<script>
    // Injeção limpa de dados vindos do PHP para o objeto global do JavaScript
    <?php foreach ($classes as $class): ?>
        window['classData_<?= $class['id'] ?>'] = {
            id         : <?= $class['id'] ?>,
            title      : <?= json_encode($class['title']) ?>,
            day_of_week: <?= json_encode($class['day_of_week']) ?>,
            time_start : <?= json_encode($class['time_start']) ?>,
            time_end   : <?= json_encode($class['time_end']) ?>,
            location   : <?= json_encode($class['location']) ?>,
            trainer_id : <?= $class['trainer_id'] ?>,
            total_spots: <?= $class['total_spots'] ?>,
            type       : <?= json_encode($class['type']) ?>
        };
    <?php endforeach; ?>

    function showModal(id) { document.getElementById(id).classList.add('show'); }
    function hideModal(id) { document.getElementById(id).classList.remove('show'); }

    function openEditModal(id) {
        const d = window['classData_' + id];
        if (!d) return;
        document.getElementById('e-id').value       = d.id;
        document.getElementById('e-title').value    = d.title;
        document.getElementById('e-type').value     = d.type;
        document.getElementById('e-day').value      = d.day_of_week;
        document.getElementById('e-start').value    = d.time_start;
        document.getElementById('e-end').value      = d.time_end;
        document.getElementById('e-location').value = d.location;
        document.getElementById('e-trainer').value  = d.trainer_id;
        document.getElementById('e-spots').value    = d.total_spots;
        showModal('edit-modal');
    }
</script>