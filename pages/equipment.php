<?php
session_start();
require_once __DIR__ . '/../config/database.php';
// Importa o template limpo
require_once __DIR__ . '/../templates/equipment.tpl.php';

$current_page = 'equipment';
$page_title = 'Equipment';
$extra_css = '../css/equipment.css';

// Mudar status via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['equipment_id'], $_POST['status'])) {
    validate_csrf();

    $allowed = ['available', 'full', 'maintenance'];
    if (in_array($_POST['status'], $allowed)) {
        $stmt = $db->prepare("UPDATE equipment SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], $_POST['equipment_id']]);
    }
    header('Location: equipment.php');
    exit;
}

// Lógica de recolha de dados
$equipment = $db->query("SELECT * FROM equipment ORDER BY category, name")->fetchAll(PDO::FETCH_ASSOC);
$categories = $db->query("SELECT DISTINCT category FROM equipment ORDER BY category")->fetchAll(PDO::FETCH_ASSOC);

// Renderização estrutural (Header, Template Visual e Footer)
require_once __DIR__ . '/../templates/header.php';

drawEquipmentPage($equipment, $categories);

require_once __DIR__ . '/../templates/footer.php';
?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btns  = document.querySelectorAll('.filter-btn-eq');
        const cards = document.querySelectorAll('.equipment-card');
     
        btns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove o estado ativo dos outros botões e aplica ao clicado
                btns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const filter = btn.dataset.filter;
                
                // Filtra os quadradinhos no ecrã de forma instantânea
                cards.forEach(card => {
                    if (filter === 'all' || card.dataset.category === filter) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    });
</script>