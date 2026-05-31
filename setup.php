<?php
// Run this once to create the database: php setup.php

$db_path = __DIR__ . '/database/fithub.db';

if (!is_dir(__DIR__ . '/database')) {
    mkdir(__DIR__ . '/database', 0755, true);
}

$db = new PDO("sqlite:$db_path");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA journal_mode=WAL');
$db->exec('PRAGMA foreign_keys=ON');

$db->exec(file_get_contents(__DIR__ . '/sql/schema.sql'));
$db->exec(file_get_contents(__DIR__ . '/sql/seed.sql'));

// Tables created at runtime by the app
$db->exec("CREATE TABLE IF NOT EXISTS pt_bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    trainer_id INTEGER NOT NULL,
    booking_date TEXT NOT NULL,
    time_slot TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (trainer_id) REFERENCES trainers(id),
    UNIQUE(trainer_id, booking_date, time_slot)
)");

// Assign Free plan to any user without a subscription
$db->exec("INSERT OR IGNORE INTO user_subscriptions (user_id, plan_id)
           SELECT id, 1 FROM users");

echo "Database created successfully at $db_path\n";
