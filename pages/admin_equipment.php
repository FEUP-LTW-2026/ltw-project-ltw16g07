<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../templates/admin_equipment.tpl.php';

require_admin();

$current_page = 'admin';
$extra_css    = '../css/admin.css';
$extra_js     = '../javascript/admin_equipment.js';
$page_title   = 'Manage Equipment';

$ALLOWED_STATUSES = ['available', 'full', 'maintenance'];

function normalize_status(string $status): string
{
    global $ALLOWED_STATUSES;
    return in_array($status, $ALLOWED_STATUSES, true) ? $status : 'available';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    if (isset($_POST['add_equipment'])) {
        $name        = trim($_POST['name'] ?? '');
        $category    = trim($_POST['category'] ?? '');
        $quantity    = max(1, (int) ($_POST['quantity'] ?? 1));
        $description = trim($_POST['description'] ?? '');
        $status      = normalize_status($_POST['status'] ?? 'available');

        if ($name && $category) {
            $db->prepare("INSERT INTO equipment (name, category, quantity, description, status) VALUES (?, ?, ?, ?, ?)")
               ->execute([$name, $category, $quantity, $description, $status]);
            $_SESSION['flash_success'] = 'Equipment added successfully.';
        } else {
            $_SESSION['flash_error'] = 'Please fill in at least the name and category.';
        }
    }

    if (isset($_POST['edit_equipment'])) {
        $id          = (int) ($_POST['equipment_id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $category    = trim($_POST['category'] ?? '');
        $quantity    = max(1, (int) ($_POST['quantity'] ?? 1));
        $description = trim($_POST['description'] ?? '');
        $status      = normalize_status($_POST['status'] ?? 'available');

        if ($id && $name && $category) {
            $db->prepare("UPDATE equipment SET name=?, category=?, quantity=?, description=?, status=? WHERE id=?")
               ->execute([$name, $category, $quantity, $description, $status, $id]);
            $_SESSION['flash_success'] = 'Equipment updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Please fill in all required fields.';
        }
    }

    if (isset($_POST['delete_equipment'])) {
        $id = (int) ($_POST['equipment_id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM equipment WHERE id = ?")->execute([$id]);
            $_SESSION['flash_success'] = 'Equipment deleted successfully.';
        }
    }

    header('Location: admin_equipment.php');
    exit;
}

$equipment = $db->query("SELECT * FROM equipment ORDER BY category, name")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../templates/header.php';

// Renderiza a página recorrendo ao template purificado
drawAdminEquipmentPage($equipment);
require_once __DIR__ . '/../templates/footer.php';
?>