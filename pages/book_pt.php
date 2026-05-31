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

// --- SEQUÊNCIA DE RENDERIZAÇÃO ---
require_once __DIR__ . '/../templates/header.php';

// Chamada da função que desenha a página, injetando o array do PT necessário
drawBookPtPage($trainer);

require_once __DIR__ . '/../templates/footer.php';
?>