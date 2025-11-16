<?php
require_once __DIR__ . '/../core/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Simple citizenship list - can be expanded to use a database table
    $citizenships = [
        ['name' => 'Filipino'],
        ['name' => 'American'],
        ['name' => 'Chinese'],
        ['name' => 'Japanese'],
        ['name' => 'Korean'],
        ['name' => 'Indian'],
        ['name' => 'British'],
        ['name' => 'Australian'],
        ['name' => 'Canadian'],
        ['name' => 'German'],
        ['name' => 'French'],
        ['name' => 'Italian'],
        ['name' => 'Spanish'],
        ['name' => 'Dutch'],
        ['name' => 'Swedish'],
        ['name' => 'Norwegian'],
        ['name' => 'Danish'],
        ['name' => 'Finnish'],
        ['name' => 'Swiss'],
        ['name' => 'Austrian'],
        ['name' => 'Belgian'],
        ['name' => 'Portuguese'],
        ['name' => 'Russian'],
        ['name' => 'Brazilian'],
        ['name' => 'Argentine'],
        ['name' => 'Mexican'],
        ['name' => 'Thai'],
        ['name' => 'Vietnamese'],
        ['name' => 'Indonesian'],
        ['name' => 'Malaysian'],
        ['name' => 'Singaporean'],
        ['name' => 'Taiwanese'],
        ['name' => 'Hong Kong'],
        ['name' => 'Other']
    ];

    echo json_encode(['status' => 'success', 'data' => $citizenships]);
} catch (Exception $e) {
    error_log("Citizenship API Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch citizenship options']);
}
?>