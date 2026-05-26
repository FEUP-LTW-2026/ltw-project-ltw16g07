<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$current_page = 'admin';
$extra_css    = '../css/admin.css';
$page_title   = 'Manage Equipment';

$ALLOWED_STATUSES = ['available', 'full', 'maintenance'];

function normalize_status(string $status): string
{
    global $ALLOWED_STATUSES;
    return in_array($status, $ALLOWED_STATUSES, true) ? $status : 'available';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

require_once __DIR__ . '/../partials/header.php';
?>

<main>
    <div class="page-header page-header--row">
        <div class="page-header-title">
            <h1>Manage Equipment</h1>
        </div>
        <div class="page-header-actions">
            <button class="btn-small btn-success" onclick="showModal('add-modal')">+ Add Equipment</button>
            <a href="admin.php" class="back-link">&larr; Back</a>
        </div>
    </div>

    <div class="admin-section">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipment as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><span class="badge-category"><?= htmlspecialchars($item['category']) ?></span></td>
                        <td><?= (int) $item['quantity'] ?></td>
                        <td>
                            <span class="badge-status <?= $item['status'] === 'available' ? 'active' : 'inactive' ?>">
                                <?= htmlspecialchars(ucfirst($item['status'] ?? 'Available')) ?>
                            </span>
                        </td>
                        <td class="cell-muted cell-desc"><?= htmlspecialchars($item['description'] ?? '') ?></td>
                        <td class="cell-actions">
                            <button class="btn-small" onclick="openEditEq(<?= $item['id'] ?>)">Edit</button>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Delete this equipment item?')">
                                <input type="hidden" name="equipment_id" value="<?= $item['id'] ?>">
                                <button type="submit" name="delete_equipment" class="btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <script>
                        window['eqData_<?= $item['id'] ?>'] = {
                            id         : <?= $item['id'] ?>,
                            name       : <?= json_encode($item['name']) ?>,
                            category   : <?= json_encode($item['category']) ?>,
                            quantity   : <?= (int) $item['quantity'] ?>,
                            description: <?= json_encode($item['description'] ?? '') ?>,
                            status     : <?= json_encode($item['status'] ?? 'available') ?>
                        };
                    </script>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Modal -->
    <div class="modal-overlay-admin" id="add-modal">
        <div class="modal-content-admin">
            <h3>Add Equipment</h3>
            <form method="POST">
                <label for="eq-name">Name <span class="required">*</span></label>
                <input type="text" name="name" id="eq-name" required>

                <label for="eq-category">Category <span class="required">*</span></label>
                <input type="text" name="category" id="eq-category" placeholder="e.g. Cardio, Strength, Flexibility" required>

                <label for="eq-quantity">Quantity</label>
                <input type="number" name="quantity" id="eq-quantity" min="1" value="1">

                <label for="eq-status">Status</label>
                <select name="status" id="eq-status">
                    <option value="available">Available</option>
                    <option value="full">Full</option>
                    <option value="maintenance">Maintenance</option>
                </select>

                <label for="eq-desc">Description</label>
                <textarea name="description" id="eq-desc" placeholder="Optional description"></textarea>

                <div class="modal-actions">
                    <button type="button" class="btn-small" onclick="hideModal('add-modal')">Cancel</button>
                    <button type="submit" name="add_equipment" class="btn-small btn-success">Add</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-overlay-admin" id="edit-modal">
        <div class="modal-content-admin">
            <h3>Edit Equipment</h3>
            <form method="POST">
                <input type="hidden" name="equipment_id" id="ee-id">

                <label for="ee-name">Name <span class="required">*</span></label>
                <input type="text" name="name" id="ee-name" required>

                <label for="ee-category">Category <span class="required">*</span></label>
                <input type="text" name="category" id="ee-category" required>

                <label for="ee-quantity">Quantity</label>
                <input type="number" name="quantity" id="ee-quantity" min="1">

                <label for="ee-status">Status</label>
                <select name="status" id="ee-status">
                    <option value="available">Available</option>
                    <option value="full">Full</option>
                    <option value="maintenance">Maintenance</option>
                </select>

                <label for="ee-desc">Description</label>
                <textarea name="description" id="ee-desc" placeholder="Optional description"></textarea>

                <div class="modal-actions">
                    <button type="button" class="btn-small" onclick="hideModal('edit-modal')">Cancel</button>
                    <button type="submit" name="edit_equipment" class="btn-small btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showModal(id) { document.getElementById(id).classList.add('show'); }
        function hideModal(id) { document.getElementById(id).classList.remove('show'); }

        function openEditEq(id) {
            const d = window['eqData_' + id];
            if (!d) return;
            document.getElementById('ee-id').value       = d.id;
            document.getElementById('ee-name').value     = d.name;
            document.getElementById('ee-category').value = d.category;
            document.getElementById('ee-quantity').value = d.quantity;
            document.getElementById('ee-desc').value     = d.description;
            document.getElementById('ee-status').value   = d.status;
            showModal('edit-modal');
        }
    </script>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
