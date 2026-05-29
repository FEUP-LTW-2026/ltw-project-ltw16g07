<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $filter_type = $_GET['type'] ?? null;
    $filter_trainer = $_GET['trainer'] ?? null;
    $filter_day = $_GET['day'] ?? null;
    $filter_time = $_GET['time'] ?? null;

    $sql = "
        SELECT c.*, t.name as trainer_name
        FROM classes c
        JOIN trainers t ON c.trainer_id = t.id
        WHERE 1=1
    ";
    $params = [];

    if ($filter_type) {
        $sql .= " AND c.type = ?";
        $params[] = $filter_type;
    }

    if ($filter_trainer) {
        $sql .= " AND t.id = ?";
        $params[] = $filter_trainer;
    }

    if ($filter_day) {
        $sql .= " AND c.day_of_week = ?";
        $params[] = $filter_day;
    }

    if ($filter_time) {
        switch ($filter_time) {
            case 'morning':
                $sql .= " AND c.time_start < '12:00'";
                break;
            case 'afternoon':
                $sql .= " AND c.time_start >= '12:00' AND c.time_start < '17:00'";
                break;
            case 'evening':
                $sql .= " AND c.time_start >= '17:00'";
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid time filter. Use morning, afternoon, or evening.']);
                exit;
        }
    }

    $sql .= " ORDER BY
        CASE c.day_of_week
            WHEN 'Monday' THEN 1
            WHEN 'Tuesday' THEN 2
            WHEN 'Wednesday' THEN 3
            WHEN 'Thursday' THEN 4
            WHEN 'Friday' THEN 5
            WHEN 'Saturday' THEN 6
            WHEN 'Sunday' THEN 7
        END,
        c.time_start";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = array_map(function ($class) {
        $remaining = (int) $class['total_spots'] - (int) $class['booked_spots'];
        return [
            'id' => (int) $class['id'],
            'title' => $class['title'],
            'day_of_week' => $class['day_of_week'],
            'time_start' => $class['time_start'],
            'time_end' => $class['time_end'],
            'location' => $class['location'],
            'type' => $class['type'],
            'trainer' => [
                'id' => (int) $class['trainer_id'],
                'name' => $class['trainer_name'],
            ],
            'spots' => [
                'total' => (int) $class['total_spots'],
                'booked' => (int) $class['booked_spots'],
                'remaining' => $remaining,
                'is_full' => $remaining <= 0,
            ],
            'rating' => (float) $class['rating'],
        ];
    }, $classes);

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error.']);
}
