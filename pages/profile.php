<?php
session_start();

// 1. INICIALIZAÇÃO DA BASE DE DADOS (Imprescindível para o header.php não falhar)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../templates/profile.tpl.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php'); 
    exit;
}

// 2. CONFIGURAÇÕES VISUAIS DO CABEÇALHO
$current_page = 'profile';
$extra_css = '../css/profile.css';
$page_title = 'My Profile';
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf(); 
}

// 3. TRATAMENTO DE SUBMISSÕES (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!password_verify($current_password, $user['password'])) {
        $_SESSION['flash_error'] = "A password atual está incorreta.";
    } elseif (strlen($new_password) < 6) {
        $_SESSION['flash_error'] = "A nova password deve ter pelo menos 6 caracteres.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['flash_error'] = "As novas passwords não coincidem.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user_id]);
        $_SESSION['flash_success'] = "Password updated successfully!";
    }
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_pfp'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $newFileName = md5(time() . $user_id) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../uploads/pfp/';
            if (!is_dir($uploadFileDir)) mkdir($uploadFileDir, 0755, true);
            
            if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                $dbRelativePath = '../uploads/pfp/' . $newFileName;
                $db->prepare("UPDATE users SET profile_pic = ? WHERE id = ?")->execute([$dbRelativePath, $user_id]);
                $_SESSION['user_pfp'] = $dbRelativePath;
                $_SESSION['flash_success'] = "Profile picture updated!";
            }
        }
    }
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_pfp'])) {
    $db->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?")->execute([$user_id]);
    $_SESSION['user_pfp'] = null;
    $_SESSION['flash_success'] = "Profile picture removed.";
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_metric'])) {
    $weight = floatval($_POST['weight'] ?? 0);
    $fat = !empty($_POST['body_fat']) ? floatval($_POST['body_fat']) : null;

    if ($weight > 0) {
        $stmt = $db->prepare("INSERT INTO member_progress (user_id, weight, body_fat) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $weight, $fat]);
        $_SESSION['flash_success'] = "Metrics logged successfully!";
    }
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_metric'])) {
    $metric_id = (int)$_POST['metric_id'];
    $db->prepare("DELETE FROM member_progress WHERE id = ? AND user_id = ?")->execute([$metric_id, $user_id]);
    $_SESSION['flash_success'] = "Record deleted.";
    header('Location: profile.php');
    exit;
}

// 4. QUERIES DE RENDERIZAÇÃO GRÁFICA
$stmt = $db->prepare("SELECT * FROM member_progress WHERE user_id = ? ORDER BY recorded_at DESC");
$stmt->execute([$user_id]);
$metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chart_data = array_reverse($metrics);
$labels = []; $weights = []; $fats = [];
foreach ($chart_data as $m) {
    $labels[]  = date('M j', strtotime($m['recorded_at']));
    $weights[] = $m['weight'];
    $fats[]    = $m['body_fat'];
}
$labels_json = json_encode($labels);
$weight_json = json_encode($weights);
$fat_json    = json_encode($fats);

$stmt = $db->prepare("SELECT pt.*, t.name AS trainer_name FROM pt_bookings pt JOIN trainers t ON pt.trainer_id = t.id WHERE pt.user_id = ? ORDER BY pt.booking_date ASC");
$stmt->execute([$user_id]);
$pt_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT b.*, c.title, c.day_of_week, c.time_start FROM bookings b JOIN classes c ON b.class_id = c.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
$stmt->execute([$user_id]);
$classes_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);


// 5. COMPOSIÇÃO DA ESTRUTURA VISUAL
require_once __DIR__ . '/../templates/header.php';

drawUserProfile($user, $metrics, $pt_bookings, $classes_bookings);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function switchTab(evt, tabId) {
    const tabContents = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabContents.length; i++) { tabContents[i].classList.remove("active"); }
    const tabBtns = document.getElementsByClassName("tab-btn");
    for (let i = 0; i < tabBtns.length; i++) { tabBtns[i].classList.remove("active"); }
    document.getElementById(tabId).classList.add("active");
    evt.currentTarget.classList.add("active");
}

<?php if (!empty($metrics)): ?>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('progressChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= $labels_json ?>,
                datasets: [
                    {
                        label: 'Weight (kg)',
                        data: <?= $weight_json ?>,
                        borderColor: '#ccff00',
                        backgroundColor: 'rgba(204, 255, 0, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Body Fat (%)',
                        data: <?= $fat_json ?>,
                        borderColor: '#a855f7',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { color: '#313131' }, ticks: { color: '#ffffff', font: { family: 'Inter' } } },
                    y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Weight (kg)', color: '#ccff00', font: { family: 'Inter', weight: 'bold' } }, grid: { color: '#313131' }, ticks: { color: '#ffffff' } },
                    y1: { type: 'linear', display: true, position: 'right', title: { display: true, text: 'Body Fat (%)', color: '#a855f7', font: { family: 'Inter', weight: 'bold' } }, grid: { drawOnChartArea: false }, ticks: { color: '#ffffff' } }
                },
                plugins: { legend: { labels: { color: '#ffffff', font: { family: 'Inter', size: 12 } } } }
            }
        });
    });
<?php endif; ?>
</script>

<?php 
require_once __DIR__ . '/../templates/footer.php'; 
?>