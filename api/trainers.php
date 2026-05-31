<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $trainer_id = $_GET['id'] ?? null;

    if ($trainer_id) {
        $stmt = $db->prepare("
            SELECT t.*, u.profile_pic
            FROM trainers t
            LEFT JOIN users u ON LOWER(u.email) = LOWER(t.email)
            WHERE t.id = ?
        ");
        $stmt->execute([$trainer_id]);
        $trainer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$trainer) {
            http_response_code(404);
            echo json_encode(['error' => 'Trainer not found.']);
            exit;
        }

        $stmt = $db->prepare("
            SELECT * FROM classes WHERE trainer_id = ?
            ORDER BY
                CASE day_of_week
                    WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2
                    WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4
                    WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6
                    WHEN 'Sunday' THEN 7
                END, time_start
        ");
        $stmt->execute([$trainer_id]);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("
            SELECT r.*, u.first_name || ' ' || u.last_name as member_name
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.trainer_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$trainer_id]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [
            'id' => (int) $trainer['id'],
            'name' => $trainer['name'],
            'specialties' => array_map('trim', explode(',', $trainer['specialties'])),
            'rating' => (float) $trainer['rating'],
            'reviews_count' => (int) $trainer['reviews_count'],
            'bio' => $trainer['bio'],
            'profile_pic' => $trainer['profile_pic'],
            'classes' => array_map(function ($class) {
                $remaining = (int) $class['total_spots'] - (int) $class['booked_spots'];
                return [
                    'id' => (int) $class['id'],
                    'title' => $class['title'],
                    'day_of_week' => $class['day_of_week'],
                    'time_start' => $class['time_start'],
                    'time_end' => $class['time_end'],
                    'location' => $class['location'],
                    'type' => $class['type'],
                    'spots' => [
                        'total' => (int) $class['total_spots'],
                        'booked' => (int) $class['booked_spots'],
                        'remaining' => $remaining,
                        'is_full' => $remaining <= 0,
                    ],
                ];
            }, $classes),
            'reviews' => array_map(function ($review) {
                return [
                    'id' => (int) $review['id'],
                    'member_name' => $review['member_name'] ?? 'Anonymous',
                    'rating' => (int) $review['rating'],
                    'comment' => $review['comment'],
                    'created_at' => $review['created_at'],
                ];
            }, $reviews),
        ];

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    } else {
        $trainers = $db->query("
            SELECT t.*, u.profile_pic
            FROM trainers t
            LEFT JOIN users u ON LOWER(u.email) = LOWER(t.email)
            ORDER BY t.name
        ")->fetchAll(PDO::FETCH_ASSOC);

        $result = array_map(function ($trainer) {
            return [
                'id' => (int) $trainer['id'],
                'name' => $trainer['name'],
                'specialties' => array_map('trim', explode(',', $trainer['specialties'])),
                'rating' => (float) $trainer['rating'],
                'reviews_count' => (int) $trainer['reviews_count'],
                'bio' => $trainer['bio'],
                'profile_pic' => $trainer['profile_pic'],
            ];
        }, $trainers);

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error.']);
}
