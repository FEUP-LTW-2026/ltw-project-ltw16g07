<?php 
declare(strict_types = 1); 

function drawHomePage(array $featured_trainers, array $class_types, array $stats, string $page_title) { 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= h($page_title) ?></title>
    <link rel="icon" type="image/png" href="https://i.imgur.com/WT1OtgQ.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/landing.css">
</head>
<body>

    <nav class="landing-nav">
        <h2>Fit<span>Hub</span></h2>
        <ul>
            <li><a href="#classes">Classes</a></li>
            <li><a href="#trainers">Trainers</a></li>
            <li><a href="#plans">Plans</a></li>
        </ul>
        <div class="nav-auth">
            <a href="login.php" class="btn-login">Login</a>
            <a href="register.php" class="btn-signup">Sign Up</a>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to FitHub</h1>
            <p class="hero-subtitle" id="typing-subtitle" data-text="Your journey to a healthier, stronger you starts here. Track your workouts, join dynamic classes, and reach your goals with elite trainers."></p>
            <div class="hero-buttons">
                <a href="register.php" class="btn-signup">Get Started</a>
                <a href="#plans" class="btn-login">View Plans</a>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= (int)$stats['classes'] ?></span>
                    <span class="stat-label">Classes Available</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= (int)$stats['trainers'] ?></span>
                    <span class="stat-label">Elite Trainers</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= (int)$stats['equipment'] ?></span>
                    <span class="stat-label">Modern Equipment</span>
                </div>
            </div>
        </div>
    </section>

    <section class="classes-overview" id="classes">
        <h2>Our Classes</h2>
        <div class="classes-grid">
            <?php foreach ($class_types as $type): ?>
                <div class="class-card">
                    <h3><?= h($type['type']) ?></h3>
                    <p>Experience our specialized high-energy training routines tailored for your ultimate fitness goals.</p>
                    <a href="login.php" class="class-link">Book Class →</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="trainers-overview" id="trainers">
        <h2>Meet Our Elite Trainers</h2>
        <div class="trainers-grid">
            <?php foreach ($featured_trainers as $trainer): ?>
                <div class="trainer-card">
                    <div class="trainer-photo">
                        <?= h($trainer['photo_initials'] ?? 'PT') ?>
                    </div>
                    <h3><?= h($trainer['name']) ?></h3>
                    <p class="trainer-specialties"><?= h($trainer['specialties']) ?></p>
                    <div class="trainer-rating">★ <?= h((string)$trainer['rating']) ?> / 5</div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="plans-section" id="plans">
        <h2>Membership Plans</h2>
        <div class="plans-grid">
            <div class="plan-card">
                <h3>Basic</h3>
                <div class="plan-price">€29<span>/month</span></div>
                <ul class="plan-features">
                    <li>Access to gym floor</li>
                    <li>Standard equipment</li>
                    <li>Locker room access</li>
                </ul>
                <button class="btn-plan" onclick="openPlanModal('basic')">View Details</button>
            </div>
            <div class="plan-card featured">
                <div class="plan-badge">Popular</div>
                <h3>Monthly</h3>
                <div class="plan-price">€49<span>/month</span></div>
                <ul class="plan-features">
                    <li>Unlimited classes</li>
                    <li>Full equipment access</li>
                    <li>Free fitness assessment</li>
                </ul>
                <button class="btn-plan" onclick="openPlanModal('monthly')">View Details</button>
            </div>
            <div class="plan-card">
                <h3>Annual</h3>
                <div class="plan-price">€39<span>/month</span></div>
                <ul class="plan-features">
                    <li>2 months free</li>
                    <li>1 Personal Training session</li>
                    <li>Priority class booking</li>
                </ul>
                <button class="btn-plan" onclick="openPlanModal('annual')">View Details</button>
            </div>
        </div>
    </section>

    <div class="modal-overlay" id="plan-modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closePlanModal()">×</button>
            <h3 id="modal-plan-name">Plan Name</h3>
            <div class="modal-price" id="modal-plan-price">€00/month</div>
            <p class="modal-note" id="modal-plan-note"></p>
            <ul class="modal-features-list" id="modal-plan-features"></ul>
            <a href="register.php" class="btn-modal-action">Select Plan</a>
        </div>
    </div>

    <footer class="landing-footer">
        <p>© <?= date('Y') ?> FitHub. All rights reserved.</p>
        <p>Built for fitness excellence.</p>
    </footer>
</body>
</html>
<?php 
} 
?>