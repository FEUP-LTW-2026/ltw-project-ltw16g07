<?php 
declare(strict_types = 1); 
?>

<?php function drawAdminEquipmentPage(array $equipment) { ?>
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
                        <td><?= h($item['name']) ?></td>
                        <td><span class="badge-category"><?= h($item['category']) ?></span></td>
                        <td><?= (int) $item['quantity'] ?></td>
                        <td>
                            <span class="badge-status <?= $item['status'] === 'available' ? 'active' : 'inactive' ?>">
                                <?= h(ucfirst($item['status'] ?? 'Available')) ?>
                            </span>
                        </td>
                        <td class="cell-muted cell-desc"><?= h($item['description'] ?? '') ?></td>
                        <td class="cell-actions">
                            <button class="btn-small btn-edit-eq" 
                                    data-id="<?= $item['id'] ?>"
                                    data-name="<?= h($item['name']) ?>"
                                    data-category="<?= h($item['category']) ?>"
                                    data-quantity="<?= (int) $item['quantity'] ?>"
                                    data-description="<?= h($item['description'] ?? '') ?>"
                                    data-status="<?= h($item['status'] ?? 'available') ?>"
                                    onclick="openEditEq(<?= $item['id'] ?>)">Edit</button>
                            <form method="POST" action="../actions/admin_equipment.php" class="inline-form" onsubmit="return confirm('Delete this equipment item?')">
                                <?php csrf_input(); ?> 
                                <input type="hidden" name="equipment_id" value="<?= $item['id'] ?>">
                                <button type="submit" name="delete_equipment" class="btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="modal-overlay-admin" id="add-modal">
        <div class="modal-content-admin">
            <h3>Add Equipment</h3>
            <form method="POST" action="../actions/admin_equipment.php">
                <?php csrf_input(); ?> 
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

    <div class="modal-overlay-admin" id="edit-modal">
        <div class="modal-content-admin">
            <h3>Edit Equipment</h3>
            <form method="POST" action="../actions/admin_equipment.php">
                <?php csrf_input(); ?> 
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
</main>
<?php } ?>