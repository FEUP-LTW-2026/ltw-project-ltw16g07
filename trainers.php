<?php
session_start();
require_once __DIR__ . '/config/database.php';

$current_page = 'trainers';
$extra_css = 'profile-trainers.css';
$page_title = 'Trainers';

$trainer_id = $_GET['id'] ?? 1;

$stmt = $db->prepare("SELECT * FROM trainers WHERE id = ?");
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

$all_trainers = $db->query("SELECT id, name, photo_initials FROM trainers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$specialties = explode(',', $trainer['specialties']);

require_once __DIR__ . '/partials/header.php';
?>

<main class="pagina">
    <!-- LEFT CARD -->
    <section class="cartao cartao-perfil">
        <div class="foto"><?= htmlspecialchars($trainer['photo_initials']) ?></div>
        <h2><?= htmlspecialchars($trainer['name']) ?></h2>
        <span class="selo">Certified Trainer</span>

        <div class="etiquetas">
            <?php foreach ($specialties as $spec): ?>
                <span><?= htmlspecialchars(trim($spec)) ?></span>
            <?php endforeach; ?>
        </div>

        <div class="nota">
            <h3><?= $trainer['rating'] ?></h3>
            <div class="estrelas"><?= str_repeat('★', round($trainer['rating'])) . str_repeat('☆', 5 - round($trainer['rating'])) ?></div>
            <p>from <?= $trainer['reviews_count'] ?> reviews</p>
        </div>

        <button class="reservar">Book Personal Training</button>
    </section>

    <section class="conteudo">
        <div class="sobre">
            <h2>About</h2>
            <p><?= htmlspecialchars($trainer['bio']) ?></p>
        </div>

        <div class="aulas">
            <h2>Classes This Week</h2>
            <?php if (empty($classes)): ?>
                <p style="color: var(--text-gray);">No classes scheduled this week.</p>
            <?php else: ?>
                <?php foreach ($classes as $class): 
                    $remaining = $class['total_spots'] - $class['booked_spots'];
                    $is_full = $remaining <= 0;
                ?>
                    <div class="item-aula">
                        <div>
                            <h3><?= htmlspecialchars($class['title']) ?></h3>
                            <p><?= htmlspecialchars($class['day_of_week']) ?> · <?= htmlspecialchars($class['time_start']) ?> – <?= htmlspecialchars($class['time_end']) ?> · <?= htmlspecialchars($class['location']) ?></p>
                            <span class="vagas <?= $is_full ? 'vermelho' : 'verde' ?>"><?= $class['booked_spots'] ?> / <?= $class['total_spots'] ?> spots</span>
                        </div>
                        <?php if ($is_full): ?>
                            <button class="btn-contorno pequeno">Waitlist</button>
                        <?php else: ?>
                            <button class="btn-principal pequeno">Enroll</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="avaliacoes">
            <h2>Recent Reviews</h2>

            <?php if (empty($reviews)): ?>
                <p style="color: var(--text-gray);">No reviews yet.</p>
            <?php else: ?>
                <div class="lista-avaliacoes">
                    <?php foreach ($reviews as $review): ?>
                        <div class="avaliacao">
                            <h3>Member</h3>
                            <div class="estrelas"><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?></div>
                            <p><?= htmlspecialchars($review['comment']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <button class="btn-contorno">Leave a Review</button>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
