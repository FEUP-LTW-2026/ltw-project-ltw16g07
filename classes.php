<?php
session_start();
require_once __DIR__ . '/config/database.php';

$current_page = 'classes';
$extra_css = 'classes.css';

$page_title = 'Classes';

$filter_type = $_GET['type'] ?? 'All';
$filter_trainer = $_GET['trainer'] ?? '';
$filter_day = $_GET['day'] ?? '';
$filter_time = $_GET['time'] ?? '';

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

$days_order = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$classes_by_day = [];
foreach ($classes as $class) {
    $classes_by_day[$class['day_of_week']][] = $class;
}

$trainers = $db->query("SELECT id, name FROM trainers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$types = $db->query("SELECT DISTINCT type FROM classes ORDER BY type")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/partials/header.php';
?>

<nav id="menu">
    <h1>Class Schedule</h1>
    <p><?= count($classes) ?> classes this week</p>
    
    <div class="filter-row">
        <!-- Filtro por tipo (botões) -->
        <ul>
            <li><a href="classes.php?type=All" class="filter-btn <?= $filter_type === 'All' ? 'active' : '' ?>">All</a></li>
            <?php foreach ($types as $t): ?>
                <li>
                    <a href="classes.php?type=<?= urlencode($t['type']) ?><?= $filter_trainer ? '&trainer=' . $filter_trainer : '' ?><?= $filter_day ? '&day=' . $filter_day : '' ?><?= $filter_time ? '&time=' . $filter_time : '' ?>" 
                       class="filter-btn <?= $filter_type === $t['type'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($t['type']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Filtros dropdown -->
        <div class="filters">
            <form action="classes.php" method="GET">
                <?php if ($filter_type !== 'All'): ?>
                    <input type="hidden" name="type" value="<?= htmlspecialchars($filter_type) ?>">
                <?php endif; ?>
                
                <select name="trainer" id="trainer-filter" onchange="this.form.submit()">
                    <option value="">Trainer</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $filter_trainer == $t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="day" id="day-filter" onchange="this.form.submit()">
                    <option value="">Day</option>
                    <?php foreach ($days_order as $day): ?>
                        <option value="<?= $day ?>" <?= $filter_day === $day ? 'selected' : '' ?>>
                            <?= $day ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="time" id="time-filter" onchange="this.form.submit()">
                    <option value="">Time</option>
                    <option value="morning" <?= $filter_time === 'morning' ? 'selected' : '' ?>>Morning</option>
                    <option value="afternoon" <?= $filter_time === 'afternoon' ? 'selected' : '' ?>>Afternoon</option>
                    <option value="evening" <?= $filter_time === 'evening' ? 'selected' : '' ?>>Evening</option>
                </select>
            </form>
        </div>
    </div>
</nav>

<div class="schedule">
    <?php foreach ($days_order as $day): 
        $day_classes = $classes_by_day[$day] ?? [];
    ?>
        <div class="day" data-day="<?= strtolower($day) ?>">
            <h2><?= $day ?></h2>
            <div class="classes-container">
                <?php if (empty($day_classes)): ?>
                    <p style="color: var(--text-gray); padding: 1em;">No classes scheduled for this day.</p>
                <?php else: ?>
                    <?php foreach ($day_classes as $class): 
                        $remaining = $class['total_spots'] - $class['booked_spots'];
                        $is_full = $remaining <= 0;
                    ?>
                        <div class="class-card" data-type="<?= strtolower($class['type']) ?>">
                            <div class="class-info">
                                <h3 class="title"><?= htmlspecialchars($class['title']) ?></h3>
                                <p class="info"><?= htmlspecialchars($class['time_start']) ?> – <?= htmlspecialchars($class['time_end']) ?> · <?= htmlspecialchars($class['location']) ?> · <?= htmlspecialchars($class['trainer_name']) ?></p>
                                <p class="spots"><?= $class['booked_spots'] ?> / <?= $class['total_spots'] ?> spots</p>
                            </div>
                            <p class="rating">
                                <span class="stars"><?= str_repeat('★', round($class['rating'])) . str_repeat('☆', 5 - round($class['rating'])) ?></span> 
                                <?= $class['rating'] ?>
                            </p>
                            <div class="class-actions">
                                <form method="POST" action="book_class.php">
                                    <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                    <?php if ($is_full): ?>
                                        <button class="action-btn waitlist" disabled>Full</button>
                                    <?php else: ?>
                                        <button class="action-btn enroll" type="submit">Enroll →</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
