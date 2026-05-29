<?php
http_response_code(200);
header('Content-Type: application/json');

echo json_encode([
    'name' => 'FitHub Public API',
    'version' => '1.0.0',
    'endpoints' => [
        [
            'path' => '/api/schedule',
            'methods' => ['GET'],
            'description' => 'List class schedules. Optional filters: type, trainer, day, time (morning|afternoon|evening)',
        ],
        [
            'path' => '/api/trainers',
            'methods' => ['GET'],
            'description' => 'List all trainers. Use /api/trainers?id=N for detailed profile with classes and reviews.',
        ],
        [
            'path' => '/api/equipment',
            'methods' => ['GET'],
            'description' => 'List equipment availability. Optional filters: status (available|full|maintenance), category.',
        ],
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
