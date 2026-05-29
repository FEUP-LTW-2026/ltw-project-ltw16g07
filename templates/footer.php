    <footer>
        <p>&copy; <?= date('Y') ?> FitHub. All rights reserved.</p>
        <p>FEUP - LTW 2026 | Project by Group 07</p>
    </footer>
    <?php if (isset($extra_js)): ?>
        <?php if (is_array($extra_js)): ?>
            <?php foreach ($extra_js as $js): ?>
                <script src="<?= $js ?>" defer></script>
            <?php endforeach; ?>
        <?php else: ?>
            <script src="<?= $extra_js ?>" defer></script>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
