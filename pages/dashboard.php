<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/index.php');
    exit;
}

$current_page = 'home';
$extra_css = '../css/index.css';

$stats = [];

$stmt = $db->query("SELECT COUNT(*) as total FROM classes");
$stats['weekly_classes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM trainers");
$stats['trainers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM equipment");
$stats['equipment'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT ROUND(AVG(rating), 1) as avg_rating FROM trainers");
$stats['avg_rating'] = $stmt->fetch(PDO::FETCH_ASSOC)['avg_rating'];

$stmt = $db->prepare("
    SELECT c.*, t.name as trainer_name 
    FROM classes c 
    JOIN trainers t ON c.trainer_id = t.id 
    ORDER BY 
        CASE c.day_of_week 
            WHEN 'Monday' THEN 1 
            WHEN 'Tuesday' THEN 2 
            WHEN 'Wednesday' THEN 3 
            WHEN 'Thursday' THEN 4 
            WHEN 'Friday' THEN 5 
            WHEN 'Saturday' THEN 6 
            WHEN 'Sunday' THEN 7 
        END, 
        c.time_start 
    LIMIT 4
");
$stmt->execute();
$upcoming_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../partials/header.php';
?>

<section class="principal">
    <h1>Premium Fitness</h1>
    <h2>Train Smarter.<br>Feel Stronger.</h2>
    <p>Book classes, track progress, and connect with expert trainers.</p>
    <div class="botoes">
        <a href="classes.php"><button type="button" class="btn-primary">Browse Classes</button></a>
        <a href="trainers.php"><button type="button" class="btn-outline">Meet Trainers</button></a>
    </div>
</section>

<ul class="estatisticas">
    <li>
        <span class="valor"><?= $stats['weekly_classes'] ?>+</span>
        <span class="legenda">Weekly Classes</span>
    </li>
    <li>
        <span class="valor"><?= $stats['trainers'] ?>+</span>
        <span class="legenda">Expert Trainers</span>
    </li>
    <li>
        <span class="valor"><?= $stats['equipment'] ?>+</span>
        <span class="legenda">Equipment Items</span>
    </li>
    <li>
        <span class="valor"><?= $stats['avg_rating'] ?></span>
        <span class="legenda">Avg. Rating</span>
    </li>
</ul>

<div class="up-classes">
    <h3>Upcoming Classes</h3>
    <a href="classes.php">See all &rarr;</a>
</div>

<ul class="lista-aulas">
    <?php foreach ($upcoming_classes as $class): ?>
        <?php $remaining = $class['total_spots'] - $class['booked_spots']; ?>
        <li class="cartao-aula">
            <p class="titulo"><?= htmlspecialchars($class['title']) ?></p>
            <p class="info"><?= htmlspecialchars($class['day_of_week']) ?> · <?= htmlspecialchars($class['time_start']) ?> · <?= htmlspecialchars($class['location']) ?></p>
            <p class="treinador"><?= htmlspecialchars($class['trainer_name']) ?></p>
            <div class="rodape">
                <span class="vagas <?= $remaining <= 0 ? 'cheio' : '' ?>">
                    <?= $class['booked_spots'] ?>/<?= $class['total_spots'] ?> spots
                </span>
                <?php if ($remaining > 0): ?>
                    <form method="POST" action="../actions/book_class.php">
                        <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                        <button type="submit" class="reservar">Enroll</button>
                    </form>
                <?php else: ?>
                    <button type="button" class="reservar" disabled>Full</button>
                <?php endif; ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
