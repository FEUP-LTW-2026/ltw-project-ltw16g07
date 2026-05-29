<?php 
declare(strict_types = 1); 
?>

<?php function drawBookPtPage(array $trainer) { ?>
<main class="pt-booking-page-container">
    <a href="trainers.php?id=<?= $trainer['id'] ?>" class="back-to-profile-link">&larr; Back to Profile</a>

    <section class="pt-booking-container">
        <h2>Schedule with <?= h($trainer['name']) ?></h2>
        <p>Choose your preferred date and available hour block.</p>
        
        <form method="POST" class="pt-booking-form">
            <?php csrf_input(); ?>
            <div class="form-group">
                <label for="booking_date">1. Select Date:</label>
                <input type="date" id="booking_date" name="booking_date" required value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label for="time_slot">2. Select Time Slot:</label>
                <select id="time_slot" name="time_slot" required>
                    <option value="" disabled selected>Choose a time slot...</option>
                    <option value="07:00 - 08:00">07:00 - 08:00</option>
                    <option value="09:00 - 10:00">09:00 - 10:00</option>
                    <option value="11:00 - 12:00">11:00 - 12:00</option>
                    <option value="14:00 - 15:00">14:00 - 15:00</option>
                    <option value="16:00 - 17:00">16:00 - 17:00</option>
                    <option value="18:00 - 19:00">18:00 - 19:00</option>
                </select>
            </div>

            <button type="submit" class="btn-book-pt">Book Session Now</button>
        </form>
    </section>
</main>
<?php } ?>