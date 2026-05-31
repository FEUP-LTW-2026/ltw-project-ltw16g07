<?php 
declare(strict_types = 1); 
?>

<?php function drawClassesPage(
    array $classes, 
    array $classes_by_day, 
    array $days_order, 
    array $trainers, 
    array $types, 
    string $filter_type, 
    string $filter_trainer, 
    string $filter_day, 
    string $filter_time, 
    ?int $logged_trainer_id,
    array $enrolled_class_ids = [],
    int $current_page = 1,
    int $total_pages = 1
) { ?>

<div class="page-header">
    <h1>Class Schedule</h1>
    <p><?= count($classes) ?> classes this week</p>
</div>

<nav id="menu">
    <div class="filter-row">
        <ul>
            <li>
                <a href="classes.php?type=All" class="filter-btn <?= $filter_type === 'All' ? 'active' : '' ?>">All</a>
            </li>
            <?php foreach ($types as $t): ?>
                <li>
                    <a href="classes.php?type=<?= urlencode($t['type']) ?><?= $filter_trainer ? '&trainer=' . $filter_trainer : '' ?><?= $filter_day ? '&day=' . $filter_day : '' ?><?= $filter_time ? '&time=' . $filter_time : '' ?>"
                       class="filter-btn <?= $filter_type === $t['type'] ? 'active' : '' ?>">
                        <?= h($t['type']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
 
        <div class="filters">
            <form action="classes.php" method="GET">
                <?php if ($filter_type !== 'All'): ?>
                    <input type="hidden" name="type" value="<?= h($filter_type) ?>">
                <?php endif; ?>
 
                <select name="trainer" onchange="this.form.submit()">
                    <option value="">Trainer</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $filter_trainer == $t['id'] ? 'selected' : '' ?>>
                            <?= h($t['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
 
                <select name="day" onchange="this.form.submit()">
                    <option value="">Day</option>
                    <?php foreach ($days_order as $day): ?>
                        <option value="<?= $day ?>" <?= $filter_day === $day ? 'selected' : '' ?>><?= $day ?></option>
                    <?php endforeach; ?>
                </select>
 
                <select name="time" onchange="this.form.submit()">
                    <option value="">Time</option>
                    <option value="morning"   <?= $filter_time === 'morning'   ? 'selected' : '' ?>>Morning</option>
                    <option value="afternoon" <?= $filter_time === 'afternoon' ? 'selected' : '' ?>>Afternoon</option>
                    <option value="evening"   <?= $filter_time === 'evening'   ? 'selected' : '' ?>>Evening</option>
                </select>
            </form>
        </div>
    </div>
</nav>
 
<main class="schedule">
    <?php foreach ($days_order as $day):
        $day_classes = $classes_by_day[$day] ?? [];
    ?>
        <section class="day" data-day="<?= strtolower($day) ?>">
            <h2><?= $day ?></h2>
            <div class="classes-container">
                <?php if (empty($day_classes)): ?>
                    <p style="color: var(--text-gray); padding: 1em;">No classes scheduled for this day.</p>
                <?php else: ?>
                    <?php foreach ($day_classes as $class): 
                        $remaining = $class['total_spots'] - $class['booked_spots'];
                        $is_full = $remaining <= 0;
                        $is_own_class = $logged_trainer_id && (int) $class['trainer_id'] === $logged_trainer_id;
                    ?>
                        <article class="class-card" data-type="<?= strtolower($class['type']) ?>">
                            <div class="class-info">
                                <h3 class="title"><?= h($class['title']) ?></h3>
                                <p class="info">
                                    <?= h($class['time_start']) ?> – <?= h($class['time_end']) ?> ·
                                    <?= h($class['location']) ?> ·
                                    <?= h($class['trainer_name']) ?>
                                </p>
                                <p class="spots"><?= $class['booked_spots'] ?> / <?= $class['total_spots'] ?> spots</p>
                            </div>
                            <p class="rating">
                                <span class="stars"><?= str_repeat('★', (int)round($class['rating'])) . str_repeat('☆', 5 - (int)round($class['rating'])) ?></span>
                                <?= $class['rating'] ?>
                            </p>
                            <div class="class-actions">
                                <?php if ($is_own_class): ?>
                                    <span class="own-class-badge">Your class</span>
                                <?php elseif (in_array($class['id'], $enrolled_class_ids)): ?>
                                    <form method="POST" action="../actions/book_class.php">
                                        <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                        <button class="action-btn cancel" type="submit" name="action_type" value="cancel">Cancel Enrollment</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="../actions/book_class.php">
                                        <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                        <?php if ($is_full): ?>
                                            <button class="action-btn waitlist" type="submit" name="action_type" value="waitlist">Join Waitlist</button>
                                        <?php else: ?>
                                            <button class="action-btn enroll" type="submit" name="action_type" value="enroll">Enroll →</button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
</main>
<?php drawPagination($current_page, $total_pages, 'classes.php'); ?>
<?php } ?>