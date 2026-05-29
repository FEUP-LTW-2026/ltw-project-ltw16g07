<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $filter_status = $_GET['status'] ?? null;
    $filter_category = $_GET['category'] ?? null;

    $allowed_statuses = ['available', 'full', 'maintenance'];

    if ($filter_status && !in_array($filter_status, $allowed_statuses)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status filter. Use: available, full, or maintenance.']);
        exit;
    }

    $sql = "SELECT * FROM equipment WHERE 1=1";
    $params = [];

    if ($filter_status) {
        $sql .= " AND status = ?";
        $params[] = $filter_status;
    }

    if ($filter_category) {
        $sql .= " AND LOWER(category) = LOWER(?)";
        $params[] = $filter_category;
    }

    $sql .= " ORDER BY category, name";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = array_map(function ($item) {
        return [
            'id' => (int) $item['id'],
            'name' => $item['name'],
            'category' => $item['category'],
            'quantity' => (int) $item['quantity'],
            'description' => $item['description'],
            'status' => $item['status'],
        ];
    }, $equipment);

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error.']);
}
