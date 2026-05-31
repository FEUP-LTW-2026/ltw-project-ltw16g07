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

$stmt = $db->query("SELECT * FROM plans ORDER BY sort_order");
$index_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <h2 class="reveal">Why FitHub?</h2>
            <p class="section-sub reveal">A modern space designed for your fitness journey</p>

            <div class="about-hero reveal">
                <img src="uploads/gym/gym-with-indoor-cycling-equipment.jpg" alt="FitHub Gym Space" loading="lazy" id="parallax-img">
                <div class="about-hero-overlay">
                    <h3>State-of-the-Art Facilities</h3>
                    <p>1,200 sqm of premium training space with top equipment</p>
                </div>
            </div>

            <div class="features">
                <div class="feature-card reveal" style="transition-delay: 0.1s">
                    <div class="feature-icon">&#9650;</div>
                    <h3>Expert Trainers</h3>
                    <p>Certified professionals dedicated to helping you reach your goals.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.2s">
                    <div class="feature-icon">&#9733;</div>
                    <h3>Diverse Classes</h3>
                    <p>From yoga to boxing, HIIT to pilates — something for everyone.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.3s">
                    <div class="feature-icon">&#9881;</div>
                    <h3>Modern Equipment</h3>
                    <p>Top-of-the-line gear for cardio, strength, and functional training.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.4s">
                    <div class="feature-icon">&#9998;</div>
                    <h3>Easy Booking</h3>
                    <p>Book classes online, track your schedule, and manage your fitness journey.</p>
                </div>
            </div>

            <div class="about-gallery">
                <div class="gallery-item reveal from-left" style="transition-delay: 0.1s"
                     data-title="Strength Zone"
                     data-desc="Olympic platforms, squat racks, and free weights from 2kg to 50kg. Deadlift platforms and cable machines for all your lifts."
                     data-stats="12 platforms · 8 racks · 2-50kg">
                    <div class="flip-inner">
                        <div class="flip-front">
                            <img src="uploads/gym/low-angle-view-unrecognizable-muscular-build-man-preparing-lifting-barbell-health-club.jpg" alt="Strength Zone" loading="lazy">
                            <span>Strength Zone</span>
                        </div>
                        <div class="flip-back">
                            <div class="flip-back-content">
                                <h3>Strength Zone</h3>
                                <p></p>
                                <div class="flip-stats"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="gallery-item reveal" style="transition-delay: 0.2s"
                     data-title="Cardio Floor"
                     data-desc="Treadmills, stationary bikes, ellipticals, and rowing machines with personal screens and performance tracking."
                     data-stats="15 treadmills · 12 bikes · 8 ellipticals">
                    <div class="flip-inner">
                        <div class="flip-front">
                            <img src="uploads/gym/selective-focus-hand-young-sexy-woman-wearing-sportswear-smartwatch-standing-treadmill-workout-modern-gym-copy-space.jpg" alt="Cardio Floor" loading="lazy">
                            <span>Cardio Floor</span>
                        </div>
                        <div class="flip-back">
                            <div class="flip-back-content">
                                <h3>Cardio Floor</h3>
                                <p></p>
                                <div class="flip-stats"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="gallery-item reveal from-right" style="transition-delay: 0.3s"
                     data-title="Class Studios"
                     data-desc="Two spacious studios with sprung flooring, full-length mirrors, and professional sound systems."
                     data-stats="2 studios · 200m² · capacity: 40">
                    <div class="flip-inner">
                        <div class="flip-front">
                            <img src="uploads/gym/AshGerlCreative_ClassStudiosWVUpdate-101.jpg" alt="Class Studios" loading="lazy">
                            <span>Class Studios</span>
                        </div>
                        <div class="flip-back">
                            <div class="flip-back-content">
                                <h3>Class Studios</h3>
                                <p></p>
                                <div class="flip-stats"></div>
                            </div>
                        </div>
                    </div>
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
                        <div class="trainer-avatar"><?= h($trainer['photo_initials']) ?></div>
                        <h3><?= h($trainer['name']) ?></h3>
                        <div class="trainer-rating">
                            <span class="stars"><?= str_repeat('★', round($trainer['rating'])) . str_repeat('☆', 5 - round($trainer['rating'])) ?></span>
                            <span><?= $trainer['rating'] ?></span>
                        </div>
                        <div class="trainer-tags">
                            <?php foreach ($specs as $spec): ?>
                                <span><?= h(trim($spec)) ?></span>
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
                <?php foreach ($index_plans as $plan): 
                    $is_pro = strtolower($plan['name']) === 'pro';
                ?>
                <div class="plan-card <?= $is_pro ? 'plan-featured' : '' ?>" onclick="openPlanModal('<?= $plan['id'] ?>')" style="<?= $is_pro ? 'border-color: ' . h($plan['badge_color']) . ';' : '' ?>">
                    <?php if ($is_pro): ?>
                        <div class="plan-badge" style="background: <?= h($plan['badge_color']) ?>; color: #000;">Popular</div>
                    <?php endif; ?>
                    <h3 style="color: <?= h($plan['badge_color']) ?>"><?= h($plan['name']) ?></h3>
                    <div class="plan-price">
                        <?= $plan['price'] > 0 ? '&euro;' . number_format($plan['price'], 2) : 'Free' ?>
                        <?= $plan['price'] > 0 ? '<span>/month</span>' : '' ?>
                    </div>
                    <p class="plan-note" style="min-height: 40px;"><?= h($plan['description']) ?></p>
                    <ul style="text-align: left; list-style: none; padding: 0; margin: 20px 0; width: 100%;">
                        <li style="color: #898989; font-size: 0.85em; padding: 6px 0; display: flex; align-items: center; gap: 8px;">
                            <span style="color: <?= h($plan['badge_color']) ?>;">✓</span>
                            <?= $plan['max_bookings'] === null ? 'Unlimited class bookings' : ($plan['max_bookings'] > 0 ? 'Up to ' . $plan['max_bookings'] . ' class bookings' : 'No class bookings') ?>
                        </li>
                        <li style="color: #898989; font-size: 0.85em; padding: 6px 0; display: flex; align-items: center; gap: 8px;">
                            <span style="color: <?= h($plan['badge_color']) ?>;">✓</span>
                            <?= $plan['can_book_pt'] ? 'Personal training sessions' : 'No personal training' ?>
                        </li>
                        <li style="color: #898989; font-size: 0.85em; padding: 6px 0; display: flex; align-items: center; gap: 8px;">
                            <span style="color: <?= h($plan['badge_color']) ?>;">✓</span>
                            Full equipment access
                        </li>
                    </ul>
                    <button class="btn-primary" style="margin-top: auto; background: <?= h($plan['badge_color']) ?>; color: #000; border: none;" onclick="event.stopPropagation();location.href='pages/register.php'">Get Started</button>
                </div>
                <?php endforeach; ?>
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
        <?php foreach ($index_plans as $plan): ?>
        '<?= $plan['id'] ?>': {
            name: '<?= h($plan['name']) ?> Plan',
            price: '<?= $plan['price'] > 0 ? "&euro;" . number_format($plan['price'], 2) . "<span>/month</span>" : "Free" ?>',
            note: '<?= h($plan['description']) ?>',
            features: [
                '<?= $plan['max_bookings'] === null ? "Unlimited class bookings" : ($plan['max_bookings'] > 0 ? "Up to " . $plan['max_bookings'] . " class bookings" : "No class bookings") ?>',
                '<?= $plan['can_book_pt'] ? "Access to Personal Training sessions" : "No personal training access" ?>',
                'Full access to gym equipment',
                'Free locker access'
            ]
        },
        <?php endforeach; ?>
    };

    function openPlanModal(planId) {
        const p = plans[planId];
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

    // ===========================
    // About Section — Dynamic Effects
    // ===========================

    const aboutSection = document.getElementById('about');

    if (aboutSection) {
        // Scroll Reveal
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -50px 0px' });
        document.querySelectorAll('#about .reveal').forEach(el => revealObserver.observe(el));

        // Parallax Hero
        const parallaxImg = document.getElementById('parallax-img');
        if (parallaxImg) {
            window.addEventListener('scroll', () => {
                const rect = aboutSection.getBoundingClientRect();
                const offset = rect.top * 0.3;
                parallaxImg.style.transform = `translate3d(0, ${Math.max(-60, Math.min(60, offset))}px, 0)`;
            }, { passive: true });
        }

        // ===========================
        // Flip Cards
        // ===========================
        document.querySelectorAll('.gallery-item').forEach(item => {
            const back = item.querySelector('.flip-back');
            if (back) {
                const title = item.dataset.title || '';
                const desc = item.dataset.desc || '';
                const stats = item.dataset.stats || '';
                back.querySelector('h3').textContent = title;
                back.querySelector('p').textContent = desc;
                back.querySelector('.flip-stats').textContent = stats;
            }

            item.addEventListener('click', () => {
                item.classList.toggle('flipped');
            });
        });

        // ===========================
        // Typewriter Subtitle
        // ===========================
        const subtitle = aboutSection.querySelector('.section-sub');
        if (subtitle) {
            const finalText = subtitle.textContent.replace('|', '').trim();
            subtitle.textContent = '';
            let charIndex = 0;
            function typeChar() {
                if (charIndex < finalText.length) {
                    subtitle.textContent += finalText[charIndex];
                    charIndex++;
                    setTimeout(typeChar, 35 + Math.random() * 25);
                }
            }
            const typeObserver = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    typeChar();
                    typeObserver.disconnect();
                }
            }, { threshold: 0.5 });
            typeObserver.observe(subtitle);
        }
    }

    // ===========================
    // Animated Counters (Hero)
    // ===========================
    const heroStats = document.querySelectorAll('.hero-stats div span');
    if (heroStats.length) {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.textContent);
                    if (isNaN(target)) return;
                    let current = 0;
                    const step = Math.max(1, Math.floor(target / 40));
                    const interval = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            el.textContent = target;
                            clearInterval(interval);
                        } else {
                            el.textContent = current;
                        }
                    }, 30);
                    counterObserver.unobserve(el);
                }
            });
        }, { threshold: 0.5 });
        heroStats.forEach(el => counterObserver.observe(el));
    }
    </script>
</body>
</html>
