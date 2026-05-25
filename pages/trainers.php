<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$current_page = 'trainers';
$extra_css = '../css/profile-trainers.css';
$page_title = 'Trainers';

$trainer_id = $_GET['id'] ?? null;

if ($trainer_id) {
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

    require_once __DIR__ . '/../partials/header.php';
    ?>

    <main class="pagina">
    <aside class="cartao cartao-perfil">
        <div class="foto"><?= htmlspecialchars($trainer['photo_initials']) ?></div>
        <h2><?= htmlspecialchars($trainer['name']) ?></h2>
        <span class="selo">Certified Trainer</span>
 
        <ul class="etiquetas">
            <?php foreach ($specialties as $spec): ?>
                <li><span><?= htmlspecialchars(trim($spec)) ?></span></li>
            <?php endforeach; ?>
        </ul>
 
        <div class="nota">
            <h3><?= $trainer['rating'] ?></h3>
            <p class="estrelas"><?= str_repeat('★', round($trainer['rating'])) . str_repeat('☆', 5 - round($trainer['rating'])) ?></p>
            <p>from <?= $trainer['reviews_count'] ?> reviews</p>
        </div>
 
        <button class="reservar">Book Personal Training</button>
        <a href="trainers.php" class="btn-contorno" style="display:block;text-align:center;margin-top:10px;">&larr; All Trainers</a>
    </aside>
 
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
                    $is_full = ($class['total_spots'] - $class['booked_spots']) <= 0;
                ?>
                    <article class="item-aula">
                        <div>
                            <h3><?= htmlspecialchars($class['title']) ?></h3>
                            <p><?= htmlspecialchars($class['day_of_week']) ?> · <?= htmlspecialchars($class['time_start']) ?> – <?= htmlspecialchars($class['time_end']) ?> · <?= htmlspecialchars($class['location']) ?></p>
                            <span class="vagas <?= $is_full ? 'vermelho' : 'verde' ?>"><?= $class['booked_spots'] ?> / <?= $class['total_spots'] ?> spots</span>
                        </div>
                        <form method="POST" action="../actions/book_class.php">
                            <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                            <?php if ($is_full): ?>
                                <button class="btn-contorno pequeno" disabled>Full</button>
                            <?php else: ?>
                                <button class="btn-principal pequeno" type="submit">Enroll</button>
                            <?php endif; ?>
                        </form>
                    </article>
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
                        <article class="avaliacao">
                            <h3>Member</h3>
                            <p class="estrelas"><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?></p>
                            <p><?= htmlspecialchars($review['comment']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
 
            <button class="btn-contorno" onclick="toggleReviewForm()">Leave a Review</button>
 
            <form method="POST" action="../actions/add_review.php" class="review-form" id="review-form">
                <input type="hidden" name="trainer_id" value="<?= $trainer['id'] ?>">
                <div class="star-rating">
                    <span class="star" data-value="1">☆</span>
                    <span class="star" data-value="2">☆</span>
                    <span class="star" data-value="3">☆</span>
                    <span class="star" data-value="4">☆</span>
                    <span class="star" data-value="5">☆</span>
                    <input type="hidden" name="rating" id="rating-value" value="0">
                </div>
                <textarea name="comment" placeholder="Share your experience..." required></textarea>
                <button type="submit" class="btn-principal">Submit Review</button>
            </form>
        </div>
    </section>
</main>
 
<script>
function toggleReviewForm() {
    const form = document.getElementById('review-form');
    form.style.display = form.style.display === 'block' ? 'none' : 'block';
}
document.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', function() {
        const value = this.dataset.value;
        document.getElementById('rating-value').value = value;
        document.querySelectorAll('.star').forEach((s, i) => {
            s.textContent = i < value ? '★' : '☆';
        });
    });
});
</script>
 
<?php
    require_once __DIR__ . '/../partials/footer.php';
 
} else {
    $all_trainers = $db->query("SELECT * FROM trainers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    require_once __DIR__ . '/../partials/header.php';
?>
 
<main class="trainers-grid-page">
    <h1>Our Trainers</h1>
    <p class="trainers-subtitle">Meet our team of certified professionals</p>
 
    <div class="trainers-grid">
        <?php foreach ($all_trainers as $trainer):
            $specs = explode(',', $trainer['specialties']);
        ?>
            <a href="trainers.php?id=<?= $trainer['id'] ?>" class="trainer-card-link">
                <article class="trainer-card-page">
                    <div class="trainer-avatar-page"><?= htmlspecialchars($trainer['photo_initials']) ?></div>
                    <h3><?= htmlspecialchars($trainer['name']) ?></h3>
                    <div class="trainer-rating-page">
                        <span class="stars"><?= str_repeat('★', round($trainer['rating'])) . str_repeat('☆', 5 - round($trainer['rating'])) ?></span>
                        <span><?= $trainer['rating'] ?></span>
                    </div>
                    <ul class="trainer-tags-page">
                        <?php foreach ($specs as $spec): ?>
                            <li><span><?= htmlspecialchars(trim($spec)) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="trainer-bio-page"><?= htmlspecialchars(substr($trainer['bio'], 0, 80)) ?>...</p>
                </article>
            </a>
        <?php endforeach; ?>
    </div>
</main>
 
<?php
    require_once __DIR__ . '/../partials/footer.php';
}
?>
