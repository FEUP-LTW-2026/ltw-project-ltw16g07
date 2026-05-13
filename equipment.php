<?php
session_start();
require_once __DIR__ . '/config/database.php';

$current_page = 'equipment';
$page_title = 'Equipment';

$categories = $db->query("SELECT DISTINCT category FROM equipment ORDER BY category")->fetchAll(PDO::FETCH_ASSOC);

$equipment_by_category = [];
foreach ($categories as $cat) {
    $stmt = $db->prepare("SELECT * FROM equipment WHERE category = ? ORDER BY name");
    $stmt->execute([$cat['category']]);
    $equipment_by_category[$cat['category']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/partials/header.php';
?>

<h1>Equipment</h1>
<p style="color: var(--text-gray); margin-bottom: 2em;">Explore our premium fitness equipment.</p>

<?php foreach ($equipment_by_category as $category => $items): ?>
    <section style="margin-bottom: 2em;">
        <h2 style="color: var(--neon-green); margin-bottom: 0.5em;"><?= htmlspecialchars($category) ?></h2>
        <div style="display: flex; flex-wrap: wrap; gap: 1em;">
            <?php foreach ($items as $item): ?>
                <div style="flex: 1; min-width: 200px; background: var(--bg-card); border-radius: 12px; padding: 1.5em; border-left: 4px solid var(--neon-green);">
                    <h3 style="margin: 0 0 0.5em 0; background: var(--bg-card);"><?= htmlspecialchars($item['name']) ?></h3>
                    <p style="color: var(--text-gray); margin: 0; background: var(--bg-card);">
                        Quantity: <?= $item['quantity'] ?>
                    </p>
                    <?php if ($item['description']): ?>
                        <p style="color: var(--text-dark-gray); font-size: 0.85em; margin-top: 0.5em; background: var(--bg-card);">
                            <?= htmlspecialchars($item['description']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
