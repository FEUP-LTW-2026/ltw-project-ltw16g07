INSERT INTO trainers (name, email, specialties, rating, reviews_count, bio, photo_initials) VALUES
('Ana Silva', 'ana@fithub.com', 'Yoga,Pilates,HIIT', 4.9, 142, 'Certified instructor with 8+ years experience in yoga and functional training. Passionate about helping members reach their full potential.', 'AN'),
('Carlos Mendes', 'carlos@fithub.com', 'Spinning,HIIT', 4.6, 98, 'Former competitive cyclist turned instructor. Specializes in high-intensity cycling classes.', 'CM'),
('Sofia Nunes', 'sofia@fithub.com', 'Pilates,Yoga', 5.0, 156, 'Dedicated to mindful movement and core strength. Certified in Pilates and Vinyasa Yoga.', 'SN'),
('Pedro Costa', 'pedro@fithub.com', 'Boxing,HIIT', 4.2, 73, 'Professional boxing coach with 10 years of experience. Ready to push your limits.', 'PC');

INSERT INTO classes (title, day_of_week, time_start, time_end, location, trainer_id, total_spots, booked_spots, type, rating) VALUES
('Yoga Flow', 'Monday', '09:00', '10:00', 'Studio A', 1, 12, 8, 'Yoga', 4.9),
('HIIT Blast', 'Monday', '11:00', '12:00', 'Main Hall', 4, 15, 15, 'HIIT', 4.2),
('Pilates Core', 'Tuesday', '10:00', '11:00', 'Studio B', 3, 15, 4, 'Pilates', 5.0),
('Spinning HIIT', 'Tuesday', '18:00', '19:00', 'Cycle Room', 2, 20, 10, 'Spinning', 4.6),
('Boxing Basics', 'Thursday', '19:00', '20:00', 'Main Hall', 4, 20, 6, 'Boxing', 4.4),
('Morning Stretch', 'Friday', '07:30', '08:30', 'Studio A', 1, 10, 3, 'Yoga', 4.7);

INSERT INTO plans (name, description, price, max_bookings, can_book_pt, badge_color, sort_order) VALUES
('Free', 'Access to class schedule and basic features. Book up to 3 classes.', 0.00, 3, 0, '#898989', 1),
('Pro', 'Unlimited class bookings, personal training access, and priority support.', 19.99, NULL, 1, '#ccff00', 2),
('Elite', 'Everything in Pro plus exclusive workshops and 1-on-1 coaching.', 39.99, NULL, 1, '#a855f7', 3);

INSERT INTO equipment (name, category, quantity, description) VALUES
('Treadmill', 'Cardio', 12, 'Motorized treadmills with incline control'),
('Stationary Bike', 'Cardio', 15, 'Spin bikes for cycling classes'),
('Yoga Mat', 'Flexibility', 30, 'Non-slip exercise mats'),
('Dumbbell Set', 'Strength', 20, 'Adjustable dumbbells from 2kg to 20kg'),
('Boxing Bag', 'Boxing', 6, 'Heavy punching bags'),
('Kettlebell', 'Strength', 15, 'Cast iron kettlebells various weights');

INSERT INTO users (first_name, last_name, email, password, role, profile_pic) VALUES
('Admin', 'FitHub', 'admin@fithub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL),
('Ana', 'Silva', 'ana@fithub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', '../uploads/trainers-pfp/trainer_3.png'),
('Carlos', 'Mendes', 'carlos@fithub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', '../uploads/trainers-pfp/trainer_2.png'),
('Sofia', 'Nunes', 'sofia@fithub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', '../uploads/trainers-pfp/trainer_3.png'),
('Pedro', 'Costa', 'pedro@fithub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', '../uploads/trainers-pfp/trainer_4.png'),
('John', 'Doe', 'john@fithub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', NULL);

INSERT INTO reviews (trainer_id, rating, comment) VALUES
(1, 5, 'Amazing class. Ana knows how to motivate the whole group.'),
(1, 4, 'Great sessions, very professional. Would love more advanced levels.');

INSERT INTO bookings (user_id, class_id) VALUES (6, 3), (6, 5);
UPDATE classes SET booked_spots = booked_spots + 1 WHERE id IN (3, 5);
