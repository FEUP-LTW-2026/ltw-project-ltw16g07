<?php 
declare(strict_types = 1); 
?>

<?php function drawEquipmentPage(array $equipment, array $categories, int $current_page = 1, int $total_pages = 1) { ?>
<div class="page-header">
    <h1>Equipment</h1>
    <p>Main training area · Updated now</p>
    <div class="filter-row-equipment" style="margin-top: 0.75rem;">
        <button class="filter-btn-eq active" data-filter="all">All</button>
        <?php foreach ($categories as $cat): ?>
            <button class="filter-btn-eq" data-filter="<?= strtolower(h($cat['category'])) ?>">
                <?= h($cat['category']) ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<main class="equipment-grid">
    <?php foreach ($equipment as $item): 
        $status = h($item['status']);
        $catClass = strtolower(h($item['category']));
    ?>
        <article class="equipment-card" data-category="<?= $catClass ?>">
            <div class="card-body">
                <h3><?= h($item['name']) ?></h3>
                <p class="category"><?= h($item['category']) ?></p>
            </div>
            <div class="card-footer">
                <span class="status-badge <?= $status ?>"><?= ucfirst($status) ?></span>
                <span class="usage"><?= $item['quantity'] ?> units</span>
            </div>
            <form method="POST" action="../actions/equipment.php" class="status-form">
                <?php csrf_input(); ?> 
                <input type="hidden" name="equipment_id" value="<?= $item['id'] ?>">
                <select name="status" onchange="this.form.submit()">
                    <option value="available"  <?= $status === 'available'   ? 'selected' : '' ?>>Available</option>
                    <option value="full"        <?= $status === 'full'        ? 'selected' : '' ?>>Full</option>
                    <option value="maintenance" <?= $status === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                </select>
            </form>
        </article>
    <?php endforeach; ?>
</main>
<?php drawPagination($current_page, $total_pages, 'equipment.php'); ?>
<?php } ?>