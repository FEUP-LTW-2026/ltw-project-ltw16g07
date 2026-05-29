<?php
session_start();
require_once __DIR__ . '/../config/database.php';

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

$equipment = $db->query("SELECT * FROM equipment ORDER BY category, name")->fetchAll(PDO::FETCH_ASSOC);
$categories = $db->query("SELECT DISTINCT category FROM equipment ORDER BY category")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../templates/header.php';
?>

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
 
<main class="equipment-grid" id="equipment-grid">
    <?php foreach ($equipment as $item):
        $status         = $item['status'] ?? 'available';
        $category_lower = strtolower($item['category']);
    ?>
        <article class="equipment-card" data-status="<?= $status ?>" data-category="<?= h($category_lower) ?>">
            <h3><?= h($item['name']) ?></h3>
            <p class="category"><?= h($item['category']) ?></p>
            <?php if ($item['description']): ?>
                <p class="description"><?= h($item['description']) ?></p>
            <?php endif; ?>
            <div class="card-footer">
                <span class="status-badge <?= $status ?>"><?= ucfirst($status) ?></span>
                <span class="usage"><?= $item['quantity'] ?> units</span>
            </div>
            <form method="POST" class="status-form">
                <?php csrf_input(); ?> <input type="hidden" name="equipment_id" value="<?= $item['id'] ?>">
                <select name="status" onchange="this.form.submit()">
                    <option value="available"  <?= $status === 'available'   ? 'selected' : '' ?>>Available</option>
                    <option value="full"        <?= $status === 'full'        ? 'selected' : '' ?>>Full</option>
                    <option value="maintenance" <?= $status === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                </select>
            </form>
        </article>
    <?php endforeach; ?>
</main>
 
<script>
    const btns  = document.querySelectorAll('.filter-btn-eq');
    const cards = document.querySelectorAll('.equipment-card');
 
    btns.forEach(btn => {
        btn.addEventListener('click', () => {
            btns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.dataset.filter;
            cards.forEach(card => {
                card.style.display = (filter === 'all' || card.dataset.category === filter) ? '' : 'none';
            });
        });
    });
</script>
 
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
 
