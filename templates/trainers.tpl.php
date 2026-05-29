<?php 
  declare(strict_types = 1); 
?>

<?php // VISTA 1: Perfil Detalhado de um Treinador Específico ?>
<?php function drawTrainerProfile(array $trainer, array $classes, array $reviews) { ?>
<main class="pagina">
    <aside class="cartao cartao-perfil">
        <div class="foto" style="overflow: hidden; position: relative;">
            <?php if (!empty($trainer['profile_pic'])): ?>
                <img src="<?= h($trainer['profile_pic']) ?>" alt="<?= h($trainer['name']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php else: ?>
                <?= h($trainer['photo_initials']) ?>
            <?php endif; ?>
        </div>
        <h2><?= h($trainer['name']) ?></h2>
        <span class="selo">Certified Trainer</span>
 
        <ul class="etiquetas">
            <?php 
            // Fazemos o explode exatamente como no teu original para listar as especialidades
            $specs = explode(',', $trainer['specialties']);
            foreach ($specs as $spec): 
            ?>
                <li><span><?= h(trim($spec)) ?></span></li>
            <?php endforeach; ?>
        </ul>
 
        <div class="nota">
            <h3><?= $trainer['rating'] ?></h3>
            <p class="estrelas"><?= str_repeat('★', (int)round($trainer['rating'])) . str_repeat('☆', 5 - (int)round($trainer['rating'])) ?></p>
            <p>from <?= $trainer['reviews_count'] ?> reviews</p>
        </div>
 
        <div class="pt-booking-redirect-zone">
            <a href="book_pt.php?trainer_id=<?= $trainer['id'] ?>" class="btn-book-pt">
                Book 1-on-1 Personal Training
            </a>
        </div>
        
        <a href="trainers.php" class="btn-contorno" style="display:block;text-align:center;margin-top:10px;">&larr; All Trainers</a>
    </aside>
 
    <section class="conteudo">
        <div class="sobre">
            <h2>About</h2>
            <p><?= h($trainer['bio']) ?></p>
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
                            <h3><?= h($class['title']) ?></h3>
                            <p><?= h($class['day_of_week']) ?> · <?= h($class['time_start']) ?> – <?= h($class['time_end']) ?> · <?= h($class['location']) ?></p>
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
                            <p class="estrelas"><?= str_repeat('★', (int)$review['rating']) . str_repeat('☆', 5 - (int)$review['rating']) ?></p>
                            <p><?= h($review['comment']) ?></p>
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
<?php } ?>

<?php // VISTA 2: Listagem Geral (A Grelha com Todos os Treinadores) ?>
<?php function drawTrainersGrid(array $all_trainers) { ?>
<div class="page-header">
    <h1>Our Trainers</h1>
    <p>Meet our team of certified professionals</p>
</div>

<main class="trainers-grid-page">
    <div class="trainers-grid">
        <?php foreach ($all_trainers as $trainer): 
            // Recupera o array de especialidades exatamente como fazias no teu ciclo original
            $specs = explode(',', $trainer['specialties']);
        ?>
            <a href="trainers.php?id=<?= $trainer['id'] ?>" class="trainer-card-link">
                <article class="trainer-card-page">
                    <div class="trainer-avatar-page" style="overflow: hidden; position: relative;">
                        <?php if (!empty($trainer['profile_pic'])): ?>
                            <img src="<?= h($trainer['profile_pic']) ?>" alt="<?= h($trainer['name']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php else: ?>
                            <?= h($trainer['photo_initials']) ?>
                        <?php endif; ?>
                    </div>
                    <h3><?= h($trainer['name']) ?></h3>
                    <div class="trainer-rating-page">
                        <span class="stars"><?= str_repeat('★', (int)round($trainer['rating'])) . str_repeat('☆', 5 - (int)round($trainer['rating'])) ?></span>
                        <span><?= $trainer['rating'] ?></span>
                    </div>
                    <ul class="trainer-tags-page">
                        <?php foreach ($specs as $spec): ?>
                            <li><span><?= h(trim($spec)) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="trainer-bio-page"><?= h(substr($trainer['bio'], 0, 80)) ?>...</p>
                </article>
            </a>
        <?php endforeach; ?>
    </div>
</main>
<?php } ?>