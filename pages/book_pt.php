<?php
session_start();
require_once __DIR__ . '/../config/database.php';

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
$extra_css = '../css/profile-trainers.css'; // Carrega o CSS com os novos estilos
$page_title = 'Book PT Session';

// Garantir a existência da tabela no SQLite
$db->exec("CREATE TABLE IF NOT EXISTS pt_bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, trainer_id INTEGER NOT NULL,
    booking_date TEXT NOT NULL, time_slot TEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (trainer_id) REFERENCES trainers(id),
    UNIQUE(trainer_id, booking_date, time_slot)
);");

// Processar a submissão do agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                $stmt = $db->prepare("INSERT INTO pt_bookings (user_id, trainer_id, booking_date, time_slot) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $trainer_id, $b_date, $t_slot]);
                $_SESSION['flash_success'] = "PT Session booked successfully!";
                header("Location: trainers.php?id=" . $trainer_id);
                exit;
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Sorry, this time slot is already taken by another member!";
            }
        }
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<main class="pt-booking-page-container">
    <a href="trainers.php?id=<?= $trainer['id'] ?>" class="back-to-profile-link">&larr; Back to Profile</a>

    <section class="pt-booking-container">
        <h2>Schedule with <?= htmlspecialchars($trainer['name']) ?></h2>
        <p>Choose your preferred date and available hour block.</p>
        
        <form method="POST" class="pt-booking-form">
            <div class="form-group">
                <label for="booking_date">1. Select Date:</label>
                <input type="date" id="booking_date" name="booking_date" required value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label for="time_slot">2. Select Time Slot:</label>
                <select id="time_slot" name="time_slot" required>
                    <option value="" disabled selected>Choose a time slot...</option>
                    <option value="07:00 - 08:00">07:00 - 08:00</option>
                    <option value="09:00 - 10:00">09:00 - 10:00</option>
                    <option value="11:00 - 12:00">11:00 - 12:00</option>
                    <option value="14:00 - 15:00">14:00 - 15:00</option>
                    <option value="16:00 - 17:00">16:00 - 17:00</option>
                    <option value="18:00 - 19:00">18:00 - 19:00</option>
                </select>
            </div>

            <button type="submit" class="btn-book-pt">Book Session Now</button>
        </form>
    </section>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>  