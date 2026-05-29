INSERT INTO trainers (name, specialties, rating, reviews_count, bio, photo_initials) VALUES
('Ana Silva', 'Yoga,Pilates,HIIT', 4.9, 142, 'Certified instructor with 8+ years experience in yoga and functional training. Passionate about helping members reach their full potential.', 'AN'),
('Carlos Mendes', 'Spinning,HIIT', 4.6, 98, 'Former competitive cyclist turned instructor. Specializes in high-intensity cycling classes.', 'CM'),
('Sofia Nunes', 'Pilates,Yoga', 5.0, 156, 'Dedicated to mindful movement and core strength. Certified in Pilates and Vinyasa Yoga.', 'SN'),
('Pedro Costa', 'Boxing,HIIT', 4.2, 73, 'Professional boxing coach with 10 years of experience. Ready to push your limits.', 'PC');

INSERT INTO classes (title, day_of_week, time_start, time_end, location, trainer_id, total_spots, booked_spots, type, rating) VALUES
('Yoga Flow', 'Monday', '09:00', '10:00', 'Studio A', 1, 12, 8, 'Yoga', 4.9),
('HIIT Blast', 'Monday', '11:00', '12:00', 'Main Hall', 4, 15, 15, 'HIIT', 4.2),
('Pilates Core', 'Tuesday', '10:00', '11:00', 'Studio B', 3, 15, 4, 'Pilates', 5.0),
('Spinning HIIT', 'Tuesday', '18:00', '19:00', 'Cycle Room', 2, 20, 10, 'Spinning', 4.6),
('Boxing Basics', 'Thursday', '19:00', '20:00', 'Main Hall', 4, 20, 6, 'Boxing', 4.4),
('Morning Stretch', 'Friday', '07:30', '08:30', 'Studio A', 1, 10, 3, 'Yoga', 4.7);

INSERT INTO equipment (name, category, quantity, description) VALUES
('Treadmill', 'Cardio', 12, 'Motorized treadmills with incline control'),
('Stationary Bike', 'Cardio', 15, 'Spin bikes for cycling classes'),
('Yoga Mat', 'Flexibility', 30, 'Non-slip exercise mats'),
('Dumbbell Set', 'Strength', 20, 'Adjustable dumbbells from 2kg to 20kg'),
('Boxing Bag', 'Boxing', 6, 'Heavy punching bags'),
('Kettlebell', 'Strength', 15, 'Cast iron kettlebells various weights');

INSERT INTO reviews (trainer_id, rating, comment) VALUES
(1, 5, 'Amazing class. Ana knows how to motivate the whole group.'),
(1, 4, 'Great sessions, very professional. Would love more advanced levels.');

-- Admin user (password: password)
-- Traier user (password: password)
INSERT INTO users (first_name, last_name, email, password, role) VALUES
('Admin', 'FitHub', 'admin@fithub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Ana', 'Trainer', 'ana@fithub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer');
