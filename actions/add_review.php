<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Please login to leave a review.';
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['trainer_id'])) {
    header('Location: ../pages/trainers.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$trainer_id = (int) $_POST['trainer_id'];
$rating = (int) ($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($rating < 1 || $rating > 5) {
    $_SESSION['flash_error'] = 'Please select a rating between 1 and 5.';
    header('Location: ../pages/trainers.php?id=' . $trainer_id);
    exit;
}

if ($comment === '') {
    $_SESSION['flash_error'] = 'Please write a comment.';
    header('Location: ../pages/trainers.php?id=' . $trainer_id);
    exit;
}


$db->beginTransaction();
try {
    // 1. Inserir a review na base de dados
    $stmt = $db->prepare("INSERT INTO reviews (user_id, trainer_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $trainer_id, $rating, $comment]);

    // 2. Calcular a nova média e contagem do treinador
    $stmt = $db->prepare("SELECT ROUND(AVG(rating), 1) as avg, COUNT(*) as count FROM reviews WHERE trainer_id = ?");
    $stmt->execute([$trainer_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Atualizar o perfil do treinador
    $stmt = $db->prepare("UPDATE trainers SET rating = ?, reviews_count = ? WHERE id = ?");
    $stmt->execute([$stats['avg'], $stats['count'], $trainer_id]);

    // 4. Inserir Notificação para o ALUNO
    $db->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'review', ?)")
       ->execute([$user_id, "Your review for trainer was submitted successfully."]);


    /* ==================================================================
       SISTEMA DE BUSCA APERFEIÇOADO PARA NOTIFICAR A TREINADORA
       ================================================================== */
    
    // A. Vamos buscar o nome exato da tabela 'trainers'
    $stmtTrainerName = $db->prepare("SELECT name FROM trainers WHERE id = ?");
    $stmtTrainerName->execute([$trainer_id]);
    $trainer_data = $stmtTrainerName->fetch(PDO::FETCH_ASSOC);

    if ($trainer_data && !empty($trainer_data['name'])) {
        $trainer_fullname = trim($trainer_data['name']); // Remove espaços extras

        // B. Procuramos na tabela 'users' fazendo o merge do nome ou por aproximação de email
        // Criamos uma busca robusta em lowercase para evitar erros de letras maiúsculas
        $stmtUser = $db->prepare("
            SELECT id FROM users 
            WHERE LOWER(first_name || ' ' || last_name) = LOWER(?)
               OR LOWER(email) = LOWER(?)
               OR LOWER(first_name) = LOWER(?)
        ");
        
        // Criamos um palpite de email baseado no nome (ex: "Ana Silva" -> "ana@fithub.com")
        $email_guess = strtolower(str_replace(' ', '', $trainer_fullname)) . '@fithub.com';

        $stmtUser->execute([$trainer_fullname, $email_guess, $trainer_fullname]);
        $user_data = $stmtUser->fetch(PDO::FETCH_ASSOC);

        // C. Se encontramos o ID do utilizador correspondente, a notificação entra!
        if ($user_data) {
            $trainer_user_id = $user_data['id'];
            
            $student_name = $_SESSION['user_name'] ?? 'A member';
            $notif_message = "{$student_name} left you a new review! ⭐";

            $db->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'review', ?)")
               ->execute([$trainer_user_id, $notif_message]);
        }
    }
    
    /* ==================================================================
       FIM DO BLOCO
       ================================================================== */

    // 5. Salva tudo em segurança se não houver erros
    $db->commit();
    $_SESSION['flash_success'] = 'Review submitted!';

} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['flash_error'] = 'Something went wrong. Please try again.';
}

header('Location: ../pages/trainers.php?id=' . $trainer_id);
exit;
