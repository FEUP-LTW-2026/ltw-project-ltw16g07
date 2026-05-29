<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../templates/index.tpl.php';

// Se o utilizador já estiver autenticado, vai direto para o dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}

$page_title = 'FitHub';

// Executa as consultas para alimentar a página
$stmt = $db->query("SELECT id, name, specialties, rating, photo_initials FROM trainers WHERE id <= 4 ORDER BY rating DESC");
$featured_trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT DISTINCT type FROM classes ORDER BY type");
$class_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stats = [];
$stmt = $db->query("SELECT COUNT(*) as total FROM classes");
$stats['classes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM trainers");
$stats['trainers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM equipment");
$stats['equipment'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT ROUND(AVG(rating), 1) as avg FROM trainers");
$stats['avg_rating'] = $stmt->fetch(PDO::FETCH_ASSOC)['avg'];

// Função utilitária contra XSS
if (!function_exists('h')) {
    function h($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

// Renderiza a página chamando o template
drawHomePage($featured_trainers, $class_types, $stats, $page_title);
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ==========================================
    // Modal Interaction
    // ==========================================
    const modal = document.getElementById('plan-modal');
    const modalTitle = document.getElementById('modal-plan-name');
    const modalPrice = document.getElementById('modal-plan-price');
    const modalNote = document.getElementById('modal-plan-note');
    const modalFeaturesList = document.getElementById('modal-plan-features');

    const planDetails = {
        basic: {
            name: 'Basic Plan',
            price: '€29<span>/month</span>',
            note: 'Standard monthly entry',
            features: ['Access to gym floor', 'Standard equipment', 'Locker room access']
        },
        monthly: {
            name: 'Monthly Plan',
            price: '€49<span>/month</span>',
            note: 'Full access premium tier',
            features: ['Unlimited group classes', 'Full equipment access', 'Free fitness assessment', 'Locker room access']
        },
        annual: {
            name: 'Annual Plan',
            price: '€39<span>/month</span>',
            note: 'Billed annually (€468/year)',
            features: ['2 months entirely free', '1 Personal Training session', 'Priority booking rights', 'Full equipment access']
        }
    };

    window.openPlanModal = function(planKey) {
        const plan = planDetails[planKey];
        if (!plan || !modal) return;
        modalTitle.textContent = plan.name;
        modalPrice.innerHTML = plan.price;
        modalNote.textContent = plan.note;
        modalFeaturesList.innerHTML = '';
        plan.features.forEach(feat => {
            const li = document.createElement('li');
            li.textContent = feat;
            modalFeaturesList.appendChild(li);
        });
        modal.classList.add('show');
    };

    window.closePlanModal = function() {
        if (modal) modal.classList.remove('show');
    };

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closePlanModal();
        });
    }

    // ==========================================
    // Typing Effect (Hero Subtitle)
    // ==========================================
    const subtitle = document.getElementById('typing-subtitle');
    if (subtitle) {
        const text = subtitle.getAttribute('data-text') || '';
        let index = 0;
        function typeChar() {
            if (index < text.length) {
                subtitle.textContent += text.charAt(index);
                index++;
                setTimeout(typeChar, 15);
            }
        }
        if (typeof IntersectionObserver !== 'undefined') {
            const typeObserver = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    typeChar();
                    typeObserver.disconnect();
                }
            }, { threshold: 0.5 });
            typeObserver.observe(subtitle);
        } else {
            typeChar();
        }
    }

    // ==========================================
    // Animated Counters (Hero Stats)
    // ==========================================
    const heroStats = document.querySelectorAll('.hero-stats div span.stat-number');
    if (heroStats.length && typeof IntersectionObserver !== 'undefined') {
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
});
</script>