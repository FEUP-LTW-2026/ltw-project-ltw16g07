<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
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

require_once __DIR__ . '/../partials/header.php';
?>

<main>
    <div class="page-header page-header--row">
        <div class="page-header-title">
            <h1>Manage Classes</h1>
        </div>
        <div class="page-header-actions">
            <button class="btn-small btn-success" onclick="showModal('create-modal')">+ New Class</button>
            <a href="admin.php" class="back-link">&larr; Back</a>
        </div>
    </div>

    <div class="admin-section">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Trainer</th>
                    <th>Spots</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><?= h($class['title']) ?></td>
                        <td><span class="badge-type"><?= h($class['type']) ?></span></td>
                        <td><?= h($class['day_of_week']) ?></td>
                        <td><?= h($class['time_start']) ?> &ndash; <?= h($class['time_end']) ?></td>
                        <td><?= h($class['location']) ?></td>
                        <td><?= h($class['trainer_name']) ?></td>
                        <td><?= $class['booked_spots'] ?> / <?= $class['total_spots'] ?></td>
                        <td class="cell-actions">
                            <button class="btn-small" onclick="openEditModal(<?= $class['id'] ?>)">Edit</button>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Delete this class? All associated bookings will also be removed.')">
                                <?php csrf_input(); ?> <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                <button type="submit" name="delete_class" class="btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <script>
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
                    </script>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Create Modal -->
    <div class="modal-overlay-admin" id="create-modal">
        <div class="modal-content-admin">
            <h3>Create New Class</h3>
            <form method="POST">
                <?php csrf_input(); ?>
                <label for="c-title">Title</label>
                <input type="text" name="title" id="c-title" required>

                <label for="c-type">Type</label>
                <select name="type" id="c-type" required>
                    <option value="">Select type</option>
                    <?php foreach ($CLASS_TYPES as $t): ?>
                        <option value="<?= $t ?>"><?= $t ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="c-day">Day of Week</label>
                <select name="day_of_week" id="c-day" required>
                    <?php foreach ($DAYS_OF_WEEK as $d): ?>
                        <option value="<?= $d ?>"><?= $d ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="row-fields">
                    <div>
                        <label for="c-start">Start Time</label>
                        <input type="time" name="time_start" id="c-start" required>
                    </div>
                    <div>
                        <label for="c-end">End Time</label>
                        <input type="time" name="time_end" id="c-end" required>
                    </div>
                </div>

                <label for="c-location">Location</label>
                <input type="text" name="location" id="c-location" required>

                <label for="c-trainer">Trainer</label>
                <select name="trainer_id" id="c-trainer" required>
                    <option value="">Select trainer</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= h($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="c-spots">Total Spots</label>
                <input type="number" name="total_spots" id="c-spots" min="1" required>

                <div class="modal-actions">
                    <button type="button" class="btn-small" onclick="hideModal('create-modal')">Cancel</button>
                    <button type="submit" name="create_class" class="btn-small btn-success">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-overlay-admin" id="edit-modal">
        <div class="modal-content-admin">
            <h3>Edit Class</h3>
            <form method="POST">
                <?php csrf_input(); ?>
                <input type="hidden" name="class_id" id="e-id">

                <label for="e-title">Title</label>
                <input type="text" name="title" id="e-title" required>

                <label for="e-type">Type</label>
                <select name="type" id="e-type" required>
                    <?php foreach ($CLASS_TYPES as $t): ?>
                        <option value="<?= $t ?>"><?= $t ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="e-day">Day of Week</label>
                <select name="day_of_week" id="e-day" required>
                    <?php foreach ($DAYS_OF_WEEK as $d): ?>
                        <option value="<?= $d ?>"><?= $d ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="row-fields">
                    <div>
                        <label for="e-start">Start Time</label>
                        <input type="time" name="time_start" id="e-start" required>
                    </div>
                    <div>
                        <label for="e-end">End Time</label>
                        <input type="time" name="time_end" id="e-end" required>
                    </div>
                </div>

                <label for="e-location">Location</label>
                <input type="text" name="location" id="e-location" required>

                <label for="e-trainer">Trainer</label>
                <select name="trainer_id" id="e-trainer" required>
                    <option value="">Select trainer</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= h($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="e-spots">Total Spots</label>
                <input type="number" name="total_spots" id="e-spots" min="1" required>

                <div class="modal-actions">
                    <button type="button" class="btn-small" onclick="hideModal('edit-modal')">Cancel</button>
                    <button type="submit" name="edit_class" class="btn-small btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
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
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
