<?php 
declare(strict_types = 1); 
?>

<?php function drawAdminClassesPage(
    array $classes, 
    array $trainers, 
    array $DAYS_OF_WEEK, 
    array $CLASS_TYPES
) { ?>
<main>
    <div class="page-header page-header--row">
        <div class="page-header-title">
            <h1>Manage Classes</h1>
        </div>
        <div class="page-header-actions">
            <button class="btn-small btn-success" onclick="showModal('create-modal')">+ New Class</button>
            <a href="admin.php" class="back-link">&larr; Back</a>
        </div>
    </div>

    <div class="admin-section">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Trainer</th>
                    <th>Spots</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><?= h($class['title']) ?></td>
                        <td><span class="badge-type"><?= h($class['type']) ?></span></td>
                        <td><?= h($class['day_of_week']) ?></td>
                        <td><?= h($class['time_start']) ?> &ndash; <?= h($class['time_end']) ?></td>
                        <td><?= h($class['location']) ?></td>
                        <td><?= h($class['trainer_name']) ?></td>
                        <td><?= $class['booked_spots'] ?> / <?= $class['total_spots'] ?></td>
                        <td class="cell-actions">
                            <button class="btn-small btn-edit-class" 
                                    data-id="<?= $class['id'] ?>"
                                    data-title="<?= h($class['title']) ?>"
                                    data-type="<?= h($class['type']) ?>"
                                    data-day="<?= h($class['day_of_week']) ?>"
                                    data-start="<?= h($class['time_start']) ?>"
                                    data-end="<?= h($class['time_end']) ?>"
                                    data-location="<?= h($class['location']) ?>"
                                    data-trainer="<?= $class['trainer_id'] ?>"
                                    data-spots="<?= $class['total_spots'] ?>"
                                    onclick="openEditModal(<?= $class['id'] ?>)">Edit</button>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Delete this class? All associated bookings will also be removed.')">
                                <?php csrf_input(); ?> 
                                <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                <button type="submit" name="delete_class" class="btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="modal-overlay-admin" id="create-modal">
        <div class="modal-content-admin">
            <h3>Create New Class</h3>
            <form method="POST">
                <?php csrf_input(); ?>
                <label for="c-title">Title</label>
                <input type="text" name="title" id="c-title" required>

                <label for="c-type">Type</label>
                <select name="type" id="c-type" required>
                    <option value="">Select type</option>
                    <?php foreach ($CLASS_TYPES as $t): ?>
                        <option value="<?= $t ?>"><?= $t ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="c-day">Day of Week</label>
                <select name="day_of_week" id="c-day" required>
                    <?php foreach ($DAYS_OF_WEEK as $d): ?>
                        <option value="<?= $d ?>"><?= $d ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="row-fields">
                    <div>
                        <label for="c-start">Start Time</label>
                        <input type="time" name="time_start" id="c-start" required>
                    </div>
                    <div>
                        <label for="c-end">End Time</label>
                        <input type="time" name="time_end" id="c-end" required>
                    </div>
                </div>

                <label for="c-location">Location</label>
                <input type="text" name="location" id="c-location" required>

                <label for="c-trainer">Trainer</label>
                <select name="trainer_id" id="c-trainer" required>
                    <option value="">Select trainer</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= h($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="c-spots">Total Spots</label>
                <input type="number" name="total_spots" id="c-spots" min="1" required>

                <div class="modal-actions">
                    <button type="button" class="btn-small" onclick="hideModal('create-modal')">Cancel</button>
                    <button type="submit" name="create_class" class="btn-small btn-success">Create</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay-admin" id="edit-modal">
        <div class="modal-content-admin">
            <h3>Edit Class</h3>
            <form method="POST">
                <?php csrf_input(); ?>
                <input type="hidden" name="class_id" id="e-id">

                <label for="e-title">Title</label>
                <input type="text" name="title" id="e-title" required>

                <label for="e-type">Type</label>
                <select name="type" id="e-type" required>
                    <?php foreach ($CLASS_TYPES as $t): ?>
                        <option value="<?= $t ?>"><?= $t ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="e-day">Day of Week</label>
                <select name="day_of_week" id="e-day" required>
                    <?php foreach ($DAYS_OF_WEEK as $d): ?>
                        <option value="<?= $d ?>"><?= $d ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="row-fields">
                    <div>
                        <label for="e-start">Start Time</label>
                        <input type="time" name="time_start" id="e-start" required>
                    </div>
                    <div>
                        <label for="e-end">End Time</label>
                        <input type="time" name="time_end" id="e-end" required>
                    </div>
                </div>

                <label for="e-location">Location</label>
                <input type="text" name="location" id="e-location" required>

                <label for="e-trainer">Trainer</label>
                <select name="trainer_id" id="e-trainer" required>
                    <option value="">Select trainer</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= h($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="e-spots">Total Spots</label>
                <input type="number" name="total_spots" id="e-spots" min="1" required>

                <div class="modal-actions">
                    <button type="button" class="btn-small" onclick="hideModal('edit-modal')">Cancel</button>
                    <button type="submit" name="edit_class" class="btn-small btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php } ?>