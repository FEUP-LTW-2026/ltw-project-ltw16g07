<?php 
  declare(strict_types = 1); 
?>

<?php function drawUserProfile(array $user, array $metrics, array $pt_bookings, array $classes_bookings) { ?>
<main class="profile-container">
    <div class="profile-grid">
        <section class="profile-card user-info">
            <div class="avatar-container">
                <div class="avatar-large" style="overflow: hidden; position: relative;">
                    <?php if (!empty($user['profile_pic'])): ?>
                        <img src="<?= h($user['profile_pic']) ?>" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <?= h($user['photo_initials'] ?? '') ?>
                    <?php endif; ?>
                </div>
                <button class="btn-small" onclick="document.getElementById('pfp-modal').style.display='flex'">Update Photo</button>
            </div>
            
            <div class="info-fields">
                <h2><?= h($user['first_name'] . ' ' . $user['last_name']) ?></h2>
                <p class="role-badge"><?= h(ucfirst($user['role'])) ?></p>
                
                <div class="field-group">
                    <span class="label">Email Address</span>
                    <span class="value"><?= h($user['email']) ?></span>
                </div>
                
                <button class="btn-outline" onclick="document.getElementById('password-modal').style.display='flex'">Change Password</button>
            </div>
        </section>

        <section class="profile-card bookings-history">
            <h3>My Schedule</h3>
            
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab(event, 'classes-tab')">Classes</button>
                <button class="tab-btn" onclick="switchTab(event, 'pt-tab')">1-on-1 Sessions</button>
            </div>

            <div id="classes-tab" class="tab-content active">
                <?php if (empty($classes_bookings)): ?>
                    <p class="empty-msg">No classes booked yet.</p>
                <?php else: ?>
                    <ul class="booking-list">
                        <?php foreach ($classes_bookings as $cb): ?>
                            <li>
                                <strong><?= h($cb['title']) ?></strong>
                                <span><?= h($cb['day_of_week']) ?> @ <?= h($cb['time_start']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div id="pt-tab" class="tab-content">
                <?php if (empty($pt_bookings)): ?>
                    <p class="empty-msg">No personal training sessions scheduled.</p>
                <?php else: ?>
                    <ul class="booking-list">
                        <?php foreach ($pt_bookings as $pt): ?>
                            <li>
                                <strong>Coach: <?= h($pt['trainer_name']) ?></strong>
                                <span><?= date('M j, Y', strtotime($pt['booking_date'])) ?> (<?= h($pt['time_slot']) ?>)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>

        <section class="profile-card progress-tracking">
            <div class="card-header">
                <h3>Fitness Progress</h3>
                <button class="btn-small btn-success" onclick="document.getElementById('metrics-modal').style.display='flex'">+ Log Metrics</button>
            </div>

            <?php if (empty($metrics)): ?>
                <p class="empty-msg">No metrics recorded yet. Start tracking your weight and body fat percentage!</p>
            <?php else: ?>
                <?php
                $chart_data = array_reverse($metrics);
                $labels = []; $weights = []; $fats = [];
                foreach ($chart_data as $m) {
                    $labels[]  = date('M j', strtotime($m['recorded_at']));
                    $weights[] = $m['weight'];
                    $fats[]    = $m['body_fat'];
                }
                $labels_json = json_encode($labels);
                $weight_json = json_encode($weights);
                $fat_json    = json_encode($fats);
                ?>
                <div class="chart-container" style="position: relative; height:250px; width:100%">
                    <canvas id="progressChart" data-labels="<?= h($labels_json) ?>" data-weight="<?= h($weight_json) ?>" data-fat="<?= h($fat_json) ?>"></canvas>
                </div>

                <div class="table-wrapper">
                    <table class="metrics-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Weight (kg)</th>
                                <th>Body Fat (%)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($metrics as $m): ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($m['recorded_at'])) ?></td>
                                    <td><?= h((string)$m['weight']) ?> kg</td>
                                    <td><?= !empty($m['body_fat']) ? h((string)$m['body_fat']) . '%' : '-' ?></td>
                                    <td>
                                        <form method="POST" action="../actions/profile.php" style="display:inline;">
                                            <?php csrf_input(); ?>
                                            <input type="hidden" name="metric_id" value="<?= $m['id'] ?>">
                                            <button type="submit" name="delete_metric" class="btn-delete-icon" onclick="return confirm('Delete this record?')">✕</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<div id="password-modal" class="modal-overlay">
    <div class="modal-box">
        <h3>Change Password</h3>
        <form method="POST" action="../actions/profile.php">
            <?php csrf_input(); ?>
            <input type="password" name="current_password" placeholder="Current Password" required>
            <input type="password" name="new_password" placeholder="New Password (min 6 chars)" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <div class="modal-actions">
                <button type="submit" name="change_password" class="btn-primary">Save Changes</button>
                <button type="button" class="btn-outline" onclick="document.getElementById('password-modal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="pfp-modal" class="modal-overlay">
    <div class="modal-box">
        <h3>Update Profile Picture</h3>
        <form method="POST" action="../actions/profile.php" enctype="multipart/form-data">
            <?php csrf_input(); ?>
            <input type="file" name="profile_pic" accept="image/*" required>
            <div class="modal-actions">
                <button type="submit" name="upload_pfp" class="btn-primary">Upload Picture</button>
                <button type="button" class="btn-outline" onclick="document.getElementById('pfp-modal').style.display='none'">Cancel</button>
            </div>
        </form>
        <?php if (!empty($user['profile_pic'])): ?>
            <form method="POST" action="../actions/profile.php" style="margin-top: 15px;">
                <?php csrf_input(); ?>
                <button type="submit" name="remove_pfp" class="btn-danger-link" style="color: #ff3333; background:none; border:none; cursor:pointer; width:100%; text-align:center;">Remove Current Photo</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div id="metrics-modal" class="modal-overlay">
    <div class="modal-box">
        <h3>Log Body Metrics</h3>
        <form method="POST" action="../actions/profile.php">
            <?php csrf_input(); ?>
            <label style="display:block; margin-bottom:5px; color:#aaa;">Weight (kg)*</label>
            <input type="number" step="0.1" name="weight" placeholder="e.g. 74.5" required>
            
            <label style="display:block; margin-bottom:5px; color:#aaa; margin-top:10px;">Body Fat (%) - Optional</label>
            <input type="number" step="0.1" name="body_fat" placeholder="e.g. 14.2">
            
            <div class="modal-actions">
                <button type="submit" name="add_metric" class="btn-primary">Save Entry</button>
                <button type="button" class="btn-outline" onclick="document.getElementById('metrics-modal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>
<?php } ?>