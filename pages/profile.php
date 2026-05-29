<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php'); 
    exit;
}

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



// 1. Alterar Password
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
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $user_id]);
        $_SESSION['flash_success'] = "Password atualizada com sucesso!";
    }
    header('Location: profile.php');
    exit;
}

// 2. Upload de Foto de Perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_pfp'])) {
    if (isset($_FILES['profile_pic_file']) && $_FILES['profile_pic_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_pic_file'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $file_type = mime_content_type($file['tmp_name']);

        if ($file['size'] > $max_size) {
            $_SESSION['flash_error'] = "A imagem excede o tamanho máximo de 2MB.";
        } elseif (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_exts)) {
            $_SESSION['flash_error'] = "Formato não suportado. Use JPG, PNG, GIF ou WEBP.";
        } else {
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (!empty($user['profile_pic'])) {
                $old_file = __DIR__ . '/../' . ltrim($user['profile_pic'], '../');
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            $new_filename = 'pfp_' . $user_id . '_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;
            $db_path = '../uploads/' . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $db->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->execute([$db_path, $user_id]);

                $_SESSION['user_pfp'] = $db_path;
                $_SESSION['flash_success'] = "Foto de perfil updated com sucesso!";
            } else {
                $_SESSION['flash_error'] = "Erro ao guardar a imagem no servidor.";
            }
        }
    } else {
        $_SESSION['flash_error'] = "Nenhum ficheiro válido foi carregado.";
    }
    header('Location: profile.php');
    exit;
}

// 3. Apagar Foto de Perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_pfp'])) {
    if (!empty($user['profile_pic'])) {
        $file_to_delete = __DIR__ . '/../' . ltrim($user['profile_pic'], '../');
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
        $stmt = $db->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?");
        $stmt->execute([$user_id]);

        $_SESSION['user_pfp'] = null;
        $_SESSION['flash_success'] = "Foto de perfil removida.";
    }
    header('Location: profile.php');
    exit;
}

// 4. Adicionar Progresso (Peso / Massa Gorda)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_progress'])) {
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT);
    $body_fat = filter_input(INPUT_POST, 'body_fat', FILTER_VALIDATE_FLOAT) ?: null;

    if ($weight) {
        $stmt = $db->prepare("INSERT INTO member_progress (user_id, weight, body_fat) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $weight, $body_fat]);
    }
    header('Location: profile.php');
    exit;
}

// 5. Cancelar Inscrição na Aula
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['cancel_booking'];

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("UPDATE classes SET booked_spots = booked_spots - 1 WHERE id = (SELECT class_id FROM bookings WHERE id = ?)");
        $stmt->execute([$booking_id]);

        $stmt = $db->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
        $stmt->execute([$booking_id, $user_id]);

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
    }

    header('Location: profile.php');
    exit;
}

$stmt = $db->prepare("
    SELECT b.id as booking_id, b.created_at as booked_date,
           c.id as class_id, c.title, c.day_of_week, c.time_start, c.time_end,
           c.location, c.total_spots, c.booked_spots,
           t.name as trainer_name
    FROM bookings b
    JOIN classes c ON b.class_id = c.id
    JOIN trainers t ON c.trainer_id = t.id
    WHERE b.user_id = ?
    ORDER BY
        CASE c.day_of_week
            WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2
            WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4
            WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6
            WHEN 'Sunday' THEN 7
        END, c.time_start
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $db->prepare("SELECT weight, body_fat, recorded_at FROM member_progress WHERE user_id = ? ORDER BY recorded_at ASC");
$stmt->execute([$user_id]);
$progress_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dates = [];
$weights = [];
$fats = [];
foreach ($progress_data as $row) {
    $dates[] = date('d M', strtotime($row['recorded_at']));
    $weights[] = $row['weight'];
    $fats[] = $row['body_fat'] ?? 0;
}

require_once __DIR__ . '/../partials/header.php';
?>

<main class="profile-page">
    <section class="profile-card">
        <div class="profile-avatar-container">
            <div class="profile-avatar">
                <?php if (!empty($user['profile_pic'])): ?>
                    <img src="<?= h($user['profile_pic']) ?>" alt="Profile Picture">
                <?php else: ?>
                    <?= h($_SESSION['user_initials']) ?>
                <?php endif; ?>
            </div>
            
            <form method="POST" action="profile.php" enctype="multipart/form-data" class="profile-pic-form">
                <?php csrf_input(); ?> <input type="hidden" name="upload_pfp" value="1">
                <label for="pfp-input" class="pfp-label">
                    <span class="pfp-edit-btn">Alterar Foto</span>
                </label>
                <input type="file" name="profile_pic_file" id="pfp-input" class="pfp-file-input" onchange="this.form.submit()" accept="image/*">
            </form>
            
            <?php if (!empty($user['profile_pic'])): ?>
                <form method="POST" action="profile.php" class="profile-pic-delete-form">
                    <?php csrf_input(); ?> <input type="hidden" name="delete_pfp" value="1">
                    <button type="submit" class="pfp-delete-btn" onclick="return confirm('Remover a foto de perfil?')">Remover Foto</button>
                </form>
            <?php endif; ?>
        </div>
        <h2><?= h($user['first_name'] . ' ' . $user['last_name']) ?></h2>
        <p class="profile-email"><?= h($user['email']) ?></p>
        <p class="profile-member">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
        <p class="profile-bookings-count"><?= count($bookings) ?> class<?= count($bookings) !== 1 ? 'es' : '' ?> booked</p>
        <hr class="profile-separator">
        <article class="progress-form-container">
            <h3>Log Today's Stats</h3>
            <form method="POST" action="profile.php">
                <?php csrf_input(); ?>
                <input type="hidden" name="add_progress" value="1">
                <div class="form-group">
                    <label for="weight">Weight (kg):</label>
                    <input type="number" step="0.1" name="weight" id="weight" required placeholder="e.g. 74.5">
                </div>
                <div class="form-group">
                    <label for="body_fat">Body Fat % (optional):</label>
                    <input type="number" step="0.1" name="body_fat" id="body_fat" placeholder="e.g. 15.2">
                </div>
                <button type="submit" class="btn-primary btn-full-width">Save Progress</button>
            </form>
        </article>
    </section>

    <section class="profile-content">
        <section class="progress-tracking-section">
            <h2>Progress Tracking</h2>
            <article class="chart-container">
                <?php if (empty($progress_data)): ?>
                    <p class="empty-chart-text">No progress logged yet. Enter your stats on the left to view your chart!</p>
                <?php else: ?>
                    <canvas id="progressChart"></canvas>
                <?php endif; ?>
            </article>
        </section>
        <h2>My Bookings</h2>
        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <p>You haven't booked any classes yet.</p>
                <a href="../pages/classes.php"><button class="btn-primary">Browse Classes</button></a>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking):
                $remaining = $booking['total_spots'] - $booking['booked_spots'];
            ?>
                <div class="booking-card">
                    <div class="booking-info">
                        <h3><?= h($booking['title']) ?></h3>
                        <p class="booking-details">
                            <?= h($booking['day_of_week']) ?> ·
                            <?= h($booking['time_start']) ?> – <?= h($booking['time_end']) ?> ·
                            <?= h($booking['location']) ?>
                        </p>
                        <p class="booking-trainer">with <?= h($booking['trainer_name']) ?></p>
                        <span class="spots-badge <?= $remaining > 0 ? 'green' : 'red' ?>">
                            <?= $booking['booked_spots'] ?> / <?= $booking['total_spots'] ?> spots
                        </span>
                    </div>
                    <form method="POST" onsubmit="return confirm('Cancel this booking?')">
                        <?php csrf_input(); ?> <input type="hidden" name="cancel_booking" value="<?= $booking['booking_id'] ?>">
                        <button type="submit" class="btn-cancel">Cancel</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <section class="change-password-section" style="margin-top: 40px;">
            <h2>Change Password</h2>
            <form method="POST" action="profile.php" class="change-password-form">
                <?php csrf_input(); ?>
                <input type="hidden" name="change_password" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" name="current_password" id="current_password" required placeholder="Enter current password">
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" name="new_password" id="new_password" required placeholder="Min 6 characters">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" name="confirm_password" id="confirm_password" required placeholder="Confirm new password">
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 15px; align-self: flex-start;">Update Password</button>
            </form>
        </section>
    </section>
</main>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Só executa o script se existirem dados de progresso na base de dados
<?php if (!empty($progress_data)): ?>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('progressChart').getContext('2d');
        
        // Passar com segurança os arrays gerados no PHP para o JavaScript
        const labels = <?= json_encode($dates) ?>;
        const weightData = <?= json_encode($weights) ?>;
        const bodyFatData = <?= json_encode($fats) ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Weight (kg)',
                        data: weightData,
                        borderColor: '#ccff00', /* Cor Neon Green do teu projeto */
                        backgroundColor: 'rgba(204, 255, 0, 0.1)',
                        borderWidth: 3,
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Body Fat (%)',
                        data: bodyFatData,
                        borderColor: '#a855f7', /* Cor Purple do teu projeto */
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y1',
                        // Se a soma de todas as gorduras for 0 (vazio), esconde esta linha por padrão
                        hidden: <?= json_encode(array_sum($fats) === 0) ?>
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { color: '#313131' },
                        ticks: { color: '#ffffff', font: { family: 'Inter' } }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Weight (kg)', color: '#ccff00', font: { family: 'Inter', weight: 'bold' } },
                        grid: { color: '#313131' },
                        ticks: { color: '#ffffff' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Body Fat (%)', color: '#a855f7', font: { family: 'Inter', weight: 'bold' } },
                        grid: { drawOnChartArea: false }, // evita cruzar linhas de grelha baralhadas
                        ticks: { color: '#ffffff' }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: '#ffffff', font: { family: 'Inter', size: 12 } }
                    }
                }
            }
        });
    });
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
