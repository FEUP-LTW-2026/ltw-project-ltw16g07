<?php
session_start();
require_once __DIR__ . '/../config/database.php';
// Importação obrigatória do novo template
require_once __DIR__ . '/../templates/book_pt.tpl.php';

// Garantir que o utilizador está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$trainer_id = filter_input(INPUT_GET, 'trainer_id', FILTER_VALIDATE_INT);
if (!$trainer_id) {
    header('Location: trainers.php');
    exit;
}

// Procurar dados do treinador selecionado
$stmt = $db->prepare("SELECT * FROM trainers WHERE id = ?");
$stmt->execute([$trainer_id]);
$trainer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trainer) {
    die("Trainer not found.");
}

$current_page = 'trainers';
$extra_css = '../css/profile-trainers.css'; 
$page_title = 'Book PT Session';

if ($_SESSION['user_role'] === 'member') {
    $user_plan = get_user_plan($db, $_SESSION['user_id']);
    if (!$user_plan['can_book_pt']) {
        $_SESSION['flash_error'] = 'Personal training requires the Pro plan or higher. <a href="plans.php">Upgrade your plan</a>.';
        header('Location: ../pages/trainers.php?id=' . $trainer_id);
        exit;
    }
}

// Garantir a existência da tabela no SQLite
$db->exec("CREATE TABLE IF NOT EXISTS pt_bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, trainer_id INTEGER NOT NULL,
    booking_date TEXT NOT NULL, time_slot TEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (trainer_id) REFERENCES trainers(id),
    UNIQUE(trainer_id, booking_date, time_slot)
);");

// Processar a submissão do agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $user_id = $_SESSION['user_id'];
    $b_date = $_POST['booking_date'];
    $t_slot = $_POST['time_slot'];

    if ($b_date && $t_slot) {
        $day_of_week = date('l', strtotime($b_date)); 
        $is_valid = true;
        $error_message = "";

        // Regras da Ana (ID 1)
        if ($trainer_id == 1) { 
            $allowed_days = ['Tuesday', 'Thursday'];
            $allowed_slots = ['09:00 - 10:00', '11:00 - 12:00'];
            if (!in_array($day_of_week, $allowed_days) || !in_array($t_slot, $allowed_slots)) {
                $is_valid = false;
                $error_message = "Invalid date/time! Ana is only available on Tuesdays and Thursdays between 09:00 - 12:00.";
            }
        } 
        // Regras do Bruno (ID 2)
        else if ($trainer_id == 2) {
            $allowed_days = ['Monday', 'Wednesday'];
            $allowed_slots = ['16:00 - 17:00', '18:00 - 19:00'];
            if (!in_array($day_of_week, $allowed_days) || !in_array($t_slot, $allowed_slots)) {
                $is_valid = false;
                $error_message = "Invalid date/time! Bruno is only available on Mondays and Wednesdays between 16:00 - 19:00.";
            }
        }

        if (!$is_valid) {
            $_SESSION['flash_error'] = $error_message;
        } else {
            try {
                $db->beginTransaction();

                $stmt = $db->prepare("INSERT INTO pt_bookings (user_id, trainer_id, booking_date, time_slot) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $trainer_id, $b_date, $t_slot]);

                // Encontrar o utilizador correspondente ao treinador para lhe enviar a notificação (por email)
                $stmtUser = $db->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
                $stmtUser->execute([$trainer['email']]);
                $trainer_user = $stmtUser->fetch(PDO::FETCH_ASSOC);

                if ($trainer_user) {
                    $trainer_user_id = $trainer_user['id'];
                    $student_name = $_SESSION['user_name'] ?? 'A member';
                    $formatted_date = date('M j, Y', strtotime($b_date));
                    $trainer_msg = "{$student_name} booked a 1-on-1 session with you on {$formatted_date} at {$t_slot}.";

                    $db->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'booking', ?)")
                       ->execute([$trainer_user_id, $trainer_msg]);
                }

                $db->commit();

                $_SESSION['flash_success'] = "PT Session booked successfully!";
                header("Location: trainers.php?id=" . $trainer_id);
                exit;
            } catch (PDOException $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $_SESSION['flash_error'] = "Sorry, this time slot is already taken by another member!";
            }
        }
    }
}

// --- SEQUÊNCIA DE RENDERIZAÇÃO ---
require_once __DIR__ . '/../templates/header.php';

// Chamada da função que desenha a página, injetando o array do PT necessário
drawBookPtPage($trainer);

require_once __DIR__ . '/../templates/footer.php';
?>