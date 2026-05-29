<?php 
declare(strict_types = 1); 
?>

<?php function drawAdminUsersPage(array $users, array $ALLOWED_ROLES) { ?>
<main>
    <div class="page-header page-header--row">
        <div class="page-header-title">
            <h1>Manage Users</h1>
        </div>
        <div class="page-header-actions">
            <button class="btn-small btn-success" onclick="showModal('add-modal')">+ Add User</button>
            <a href="admin.php" class="back-link">&larr; Back</a>
        </div>
    </div>

    <div class="admin-section">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): 
                    $is_deactivated = str_starts_with($u['email'], '!deactivated_');
                    $display_email = $is_deactivated ? substr($u['email'], strlen('!deactivated_')) : $u['email'];
                ?>
                    <tr>
                        <td><?= h($u['first_name'] . ' ' . $u['last_name']) ?></td>
                        <td><?= h($display_email) ?></td>
                        <td><span class="badge-role <?= h($u['role']) ?>"><?= h($u['role']) ?></span></td>
                        <td>
                            <span class="badge-status <?= $is_deactivated ? 'inactive' : 'active' ?>">
                                <?= $is_deactivated ? 'Deactivated' : 'Active' ?>
                            </span>
                        </td>
                        <td class="cell-actions">
                            <form method="POST" class="inline-form" onsubmit="return confirm('Change status of this user account?')">
                                <?php csrf_input(); ?>
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <?php if ($is_deactivated): ?>
                                    <button type="submit" name="toggle_status" class="btn-small btn-success">Activate</button>
                                <?php else: ?>
                                    <button type="submit" name="toggle_status" class="btn-small btn-danger">Deactivate</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="modal-overlay-admin" id="add-modal">
        <div class="modal-content-admin">
            <h3>Add New User</h3>
            <form method="POST">
                <?php csrf_input(); ?>
                
                <label for="u-first">First Name <span class="required">*</span></label>
                <input type="text" name="first_name" id="u-first" required placeholder="e.g. John">

                <label for="u-last">Last Name <span class="required">*</span></label>
                <input type="text" name="last_name" id="u-last" required placeholder="e.g. Doe">

                <label for="u-email">Email Address <span class="required">*</span></label>
                <input type="email" name="email" id="u-email" required placeholder="e.g. john.doe@example.com">

                <label for="u-pass">Temporary Password <span class="required">*</span></label>
                <input type="password" name="password" id="u-pass" required placeholder="Minimum 6 characters">

                <label for="u-role">System Role</label>
                <select name="role" id="u-role">
                    <?php foreach ($ALLOWED_ROLES as $role): ?>
                        <option value="<?= $role ?>" <?= $role === 'member' ? 'selected' : '' ?>><?= ucfirst($role) ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="modal-actions">
                    <button type="button" class="btn-small" onclick="hideModal('add-modal')">Cancel</button>
                    <button type="submit" name="add_user" class="btn-small btn-success">Add User</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php } ?>