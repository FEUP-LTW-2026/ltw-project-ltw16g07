<?php if (!function_exists('drawPagination')): ?>
<?php function drawPagination(int $current_page, int $total_pages, string $base_url): void {
    if ($total_pages <= 1) return;

    $query_string = build_pagination_query_string();
    $separator = $query_string ? '&' : '?';
?>
<nav class="pagination">
    <?php if ($current_page > 1): ?>
        <a href="<?= $base_url . $separator ?>page=<?= $current_page - 1 ?><?= $query_string ? '&' . $query_string : '' ?>" class="pagination-link prev">&laquo; Previous</a>
    <?php endif; ?>

    <?php
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    for ($i = $start; $i <= $end; $i++): ?>
        <a href="<?= $base_url . $separator ?>page=<?= $i ?><?= $query_string ? '&' . $query_string : '' ?>"
           class="pagination-link <?= $i === $current_page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($current_page < $total_pages): ?>
        <a href="<?= $base_url . $separator ?>page=<?= $current_page + 1 ?><?= $query_string ? '&' . $query_string : '' ?>" class="pagination-link next">Next &raquo;</a>
    <?php endif; ?>
</nav>
<?php } ?>
<?php endif; ?>
