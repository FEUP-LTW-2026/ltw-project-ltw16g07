<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}

$page_title = 'FitHub';

$stmt = $db->query("SELECT id, name, specialties, rating, photo_initials FROM trainers WHERE id <= 4 ORDER BY rating DESC");
$featured_trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT DISTINCT type FROM classes ORDER BY type");
$class_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT COUNT(*) as total FROM classes");
$stats['classes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM trainers");
$stats['trainers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM equipment");
$stats['equipment'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT ROUND(AVG(rating), 1) as avg FROM trainers");
$stats['avg_rating'] = $stmt->fetch(PDO::FETCH_ASSOC)['avg'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link rel="icon" type="image/png" href="https://i.imgur.com/WT1OtgQ.png">
    <link rel="stylesheet" href="css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="landing-nav">
        <a href="index.php"><h2>FitHub</h2></a>
        <ul>
            <li><a href="#about">About</a></li>
            <li><a href="#trainers">Trainers</a></li>
            <li><a href="#classes">Classes</a></li>
            <li><a href="#plans">Plans</a></li>
        </ul>
        <div>
            <a href="pages/login.php"><button class="btn-outline">Login</button></a>
            <a href="pages/register.php"><button class="btn-primary">Join Free</button></a>
        </div>
    </nav>

    <section class="hero">
        <h1>PREMIUM FITNESS</h1>
        <h2 class="hero-slogan">Train Smarter.<br>Feel Stronger.</h2>
        <p class="hero-text">Book classes, track progress, and connect with expert trainers.<br>Your fitness journey starts here.</p>
        <div class="hero-buttons">
            <a href="pages/register.php"><button class="btn-primary btn-large">Get Started Free</button></a>
            <a href="pages/classes.php"><button class="btn-outline btn-large">Browse Classes</button></a>
        </div>
        <div class="hero-stats">
            <div><span><?= $stats['classes'] ?>+</span> Weekly Classes</div>
            <div><span><?= $stats['trainers'] ?>+</span> Expert Trainers</div>
            <div><span><?= $stats['equipment'] ?>+</span> Equipment Items</div>
            <div><span><?= $stats['avg_rating'] ?></span> Avg. Rating</div>
        </div>
    </section>

    <section id="about" class="section">
        <div class="section-content">
            <h2>Why FitHub?</h2>
            <div class="features">
                <div class="feature-card">
                    <div class="feature-icon">&#9650;</div>
                    <h3>Expert Trainers</h3>
                    <p>Certified professionals dedicated to helping you reach your goals.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">&#9733;</div>
                    <h3>Diverse Classes</h3>
                    <p>From yoga to boxing, HIIT to pilates — something for everyone.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">&#9881;</div>
                    <h3>Modern Equipment</h3>
                    <p>Top-of-the-line gear for cardio, strength, and functional training.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">&#9998;</div>
                    <h3>Easy Booking</h3>
                    <p>Book classes online, track your schedule, and manage your fitness journey.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="trainers" class="section section-dark">
        <div class="section-content">
            <h2>Meet Our Trainers</h2>
            <p class="section-sub">Learn from the best in the industry</p>
            <div class="trainer-grid">
                <?php foreach ($featured_trainers as $trainer):
                    $specs = explode(',', $trainer['specialties']);
                ?>
                    <div class="trainer-card">
                        <div class="trainer-avatar"><?= htmlspecialchars($trainer['photo_initials']) ?></div>
                        <h3><?= htmlspecialchars($trainer['name']) ?></h3>
                        <div class="trainer-rating">
                            <span class="stars"><?= str_repeat('★', round($trainer['rating'])) . str_repeat('☆', 5 - round($trainer['rating'])) ?></span>
                            <span><?= $trainer['rating'] ?></span>
                        </div>
                        <div class="trainer-tags">
                            <?php foreach ($specs as $spec): ?>
                                <span><?= htmlspecialchars(trim($spec)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="section-cta">
                <a href="pages/trainers.php"><button class="btn-outline">View All Trainers</button></a>
            </div>
        </div>
    </section>

    <section id="classes" class="section">
        <div class="section-content">
            <h2>Class Types</h2>
            <p class="section-sub"><?= $stats['classes'] ?> different classes every week</p>
            <div class="class-types">
                <div class="class-type-card" onclick="toggleClassDetail(this)">
                    <div class="class-type-icon yoga">&#9775;</div>
                    <h3>Yoga</h3>
                    <p class="class-short">Improve flexibility, balance, and mental clarity.</p>
                    <div class="class-detail">A practice that combines physical postures, breathing techniques, and meditation. Suitable for all levels, from beginners to advanced practitioners. Classes include Vinyasa, Hatha, and Restorative Yoga.</div>
                </div>
                <div class="class-type-card" onclick="toggleClassDetail(this)">
                    <div class="class-type-icon hiit">&#9889;</div>
                    <h3>HIIT</h3>
                    <p class="class-short">High-intensity intervals for maximum calorie burn.</p>
                    <div class="class-detail">Short bursts of intense exercise alternated with recovery periods. Burns maximum calories in minimum time. Improves cardiovascular fitness, metabolism, and endurance. Includes bodyweight and equipment-based circuits.</div>
                </div>
                <div class="class-type-card" onclick="toggleClassDetail(this)">
                    <div class="class-type-icon pilates">&#9830;</div>
                    <h3>Pilates</h3>
                    <p class="class-short">Core strength and mindful movement.</p>
                    <div class="class-detail">Low-impact exercises that strengthen muscles while improving postural alignment and flexibility. Focuses on core strength, controlled movements, and mind-body connection. Uses mat work and specialized equipment.</div>
                </div>
                <div class="class-type-card" onclick="toggleClassDetail(this)">
                    <div class="class-type-icon spinning">&#128692;</div>
                    <h3>Spinning</h3>
                    <p class="class-short">High-energy cycling workouts.</p>
                    <div class="class-detail">Indoor cycling classes set to motivating music. Simulates outdoor riding conditions with varied resistance and pace. Low-impact, high-calorie burn. Led by instructors who guide you through sprints, climbs, and intervals.</div>
                </div>
                <div class="class-type-card" onclick="toggleClassDetail(this)">
                    <div class="class-type-icon boxing">&#129354;</div>
                    <h3>Boxing</h3>
                    <p class="class-short">Build power, speed, and endurance.</p>
                    <div class="class-detail">Full-body workout combining boxing techniques with fitness training. Includes shadow boxing, bag work, and pad drills. Improves coordination, cardiovascular fitness, and stress relief. All levels welcome, no previous experience required.</div>
                </div>
            </div>
        </div>
    </section>

    <section id="plans" class="section section-dark">
        <div class="section-content">
            <h2>Membership Plans</h2>
            <p class="section-sub">Choose the plan that fits your lifestyle</p>
            <div class="plans">
                <div class="plan-card" onclick="openPlanModal('monthly')">
                    <h3>Monthly</h3>
                    <div class="plan-price">&euro;49<span>/month</span></div>
                    <ul>
                        <li>Unlimited classes</li>
                        <li>Access to equipment</li>
                        <li>Free locker</li>
                        <li>Cancel anytime</li>
                    </ul>
                    <button class="btn-primary" onclick="event.stopPropagation();location.href='pages/register.php'">Get Started</button>
                </div>
                <div class="plan-card plan-featured" onclick="openPlanModal('annual')">
                    <div class="plan-badge">Popular</div>
                    <h3>Annual</h3>
                    <div class="plan-price">&euro;39<span>/month</span></div>
                    <p class="plan-note">Billed annually (&euro;468/year)</p>
                    <ul>
                        <li>Everything in Monthly</li>
                        <li>2 months free</li>
                        <li>Free personal training session</li>
                        <li>Priority booking</li>
                    </ul>
                    <button class="btn-primary" onclick="event.stopPropagation();location.href='pages/register.php'">Get Started</button>
                </div>
                <div class="plan-card" onclick="openPlanModal('weekly')">
                    <h3>Weekly</h3>
                    <div class="plan-price">&euro;15<span>/week</span></div>
                    <ul>
                        <li>Up to 5 classes</li>
                        <li>Access to equipment</li>
                        <li>No commitment</li>
                        <li>Perfect for visitors</li>
                    </ul>
                    <button class="btn-primary" onclick="event.stopPropagation();location.href='pages/register.php'">Get Started</button>
                </div>
            </div>

            <div id="plan-modal" class="modal-overlay" onclick="closePlanModal()">
                <div class="modal-content" onclick="event.stopPropagation()">
                    <button class="modal-close" onclick="closePlanModal()">&times;</button>
                    <h3 id="modal-plan-name"></h3>
                    <div class="plan-price" id="modal-plan-price"></div>
                    <p class="plan-note" id="modal-plan-note"></p>
                    <h4>What's included:</h4>
                    <ul id="modal-plan-features"></ul>
                    <button class="btn-primary" id="modal-plan-cta" onclick="closePlanModal();location.href='pages/register.php'">Get Started</button>
                </div>
            </div>
        </div>
    </section>

    <section class="section cta-section">
        <div class="section-content">
            <h2>Ready to Transform?</h2>
            <p>Join FitHub today and start your fitness journey.</p>
            <a href="pages/register.php"><button class="btn-primary btn-large">Join Free</button></a>
        </div>
    </section>

    <footer class="landing-footer">
        <p>&copy; <?= date('Y') ?> FitHub. All rights reserved.</p>
        <p>FEUP - LTW 2026 | Project by Group 07</p>
    </footer>

    <script>
    function toggleClassDetail(card) {
        card.classList.toggle('expanded');
    }

    const plans = {
        weekly: {
            name: 'Weekly Pass',
            price: '&euro;15<span>/week</span>',
            note: '',
            features: ['Up to 5 classes per week', 'Access to all equipment', 'No commitment required', 'Perfect for visitors and trial', 'Locker access']
        },
        monthly: {
            name: 'Monthly Membership',
            price: '&euro;49<span>/month</span>',
            note: '',
            features: ['Unlimited classes', 'Full equipment access', 'Free locker', 'Cancel anytime', 'Free fitness assessment', 'Access to all class types']
        },
        annual: {
            name: 'Annual Membership',
            price: '&euro;39<span>/month</span>',
            note: 'Billed annually (&euro;468/year)',
            features: ['Everything in Monthly', '2 months free', 'Free personal training session', 'Priority booking', 'Exclusive member events', 'Guest passes (2/month)']
        }
    };

    function openPlanModal(plan) {
        const p = plans[plan];
        document.getElementById('modal-plan-name').textContent = p.name;
        document.getElementById('modal-plan-price').innerHTML = p.price;
        document.getElementById('modal-plan-note').textContent = p.note;
        document.getElementById('modal-plan-note').style.display = p.note ? '' : 'none';
        const ul = document.getElementById('modal-plan-features');
        ul.innerHTML = '';
        p.features.forEach(f => {
            const li = document.createElement('li');
            li.textContent = f;
            ul.appendChild(li);
        });
        document.getElementById('plan-modal').classList.add('show');
    }

    function closePlanModal() {
        document.getElementById('plan-modal').classList.remove('show');
    }
    </script>
</body>
</html>
