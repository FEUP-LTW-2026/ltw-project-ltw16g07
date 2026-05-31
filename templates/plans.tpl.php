<?php function drawPlansPage(array $plans, array $current_plan, int $user_bookings_count) { ?>
<main class="plans-page">
    <div class="page-header">
        <h1>Membership Plans</h1>
    </div>

    <div class="current-plan-banner">
        <p>Your current plan: <strong style="color: <?= h($current_plan['badge_color']) ?>"><?= h($current_plan['name']) ?></strong></p>
    </div>

    <div class="plans-grid">
        <?php foreach ($plans as $plan):
            $is_current = $plan['id'] == $current_plan['id'];
            $is_upgrade = $plan['sort_order'] > $current_plan['sort_order'];
            $is_downgrade = $plan['sort_order'] < $current_plan['sort_order'];
        ?>
            <article class="plan-card <?= $is_current ? 'plan-current' : '' ?>" style="<?= $is_current ? 'border-color: ' . h($plan['badge_color']) . ';' : '' ?>">
                <div class="plan-header">
                    <h2 style="color: <?= h($plan['badge_color']) ?>"><?= h($plan['name']) ?></h2>
                    <p class="plan-price">
                        <?= $plan['price'] > 0 ? '€' . number_format($plan['price'], 2) : 'Free' ?>
                        <?= $plan['price'] > 0 ? '<span class="plan-period">/month</span>' : '' ?>
                    </p>
                </div>
                <div class="plan-body">
                    <p class="plan-desc"><?= h($plan['description']) ?></p>
                    <ul class="plan-features">
                        <li class="<?= $plan['max_bookings'] === null ? 'included' : ($plan['max_bookings'] > 0 ? 'included' : 'excluded') ?>">
                            <?= $plan['max_bookings'] === null ? 'Unlimited class bookings' : ($plan['max_bookings'] > 0 ? 'Up to ' . $plan['max_bookings'] . ' class bookings' : 'No class bookings') ?>
                        </li>
                        <li class="<?= $plan['can_book_pt'] ? 'included' : 'excluded' ?>">
                            <?= $plan['can_book_pt'] ? 'Personal training sessions' : 'No personal training' ?>
                        </li>
                    </ul>
                </div>
                <div class="plan-footer">
                    <?php if ($is_current): ?>
                        <span class="plan-badge" style="background: <?= h($plan['badge_color']) ?>; color: #000;">Current Plan</span>
                        <?php if ($current_plan['max_bookings'] !== null): ?>
                            <p class="plan-usage"><?= $user_bookings_count ?> / <?= $current_plan['max_bookings'] ?> bookings used</p>
                        <?php endif; ?>
                    <?php elseif ($is_downgrade): ?>
                        <form method="POST" action="../actions/subscribe.php">
                            <?php csrf_input(); ?>
                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                            <button type="submit" class="btn-outline">Downgrade</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="../actions/subscribe.php">
                            <?php csrf_input(); ?>
                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                            <button type="submit" class="btn-primary" style="background: <?= h($plan['badge_color']) ?>; color: #000; border: none;">Upgrade</button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>
<?php } ?>
