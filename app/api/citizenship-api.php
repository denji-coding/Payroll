<?php
require_once __DIR__ . '/../core/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Simple citizenship list - can be expanded to use a database table
    $citizenships = [
        ['citizenship' => 'Filipino'],
        ['citizenship' => 'American'],
        ['citizenship' => 'Chinese'],
        ['citizenship' => 'Japanese'],
        ['citizenship' => 'Korean'],
        ['citizenship' => 'Indian'],
        ['citizenship' => 'British'],
        ['citizenship' => 'Australian'],
        ['citizenship' => 'Canadian'],
        ['citizenship' => 'German'],
        ['citizenship' => 'French'],
        ['citizenship' => 'Italian'],
        ['citizenship' => 'Spanish'],
        ['citizenship' => 'Dutch'],
        ['citizenship' => 'Swedish'],
        ['citizenship' => 'Norwegian'],
        ['citizenship' => 'Danish'],
        ['citizenship' => 'Finnish'],
        ['citizenship' => 'Swiss'],
        ['citizenship' => 'Austrian'],
        ['citizenship' => 'Belgian'],
        ['citizenship' => 'Portuguese'],
        ['citizenship' => 'Russian'],
        ['citizenship' => 'Brazilian'],
        ['citizenship' => 'Argentine'],
        ['citizenship' => 'Mexican'],
        ['citizenship' => 'Thai'],
        ['citizenship' => 'Vietnamese'],
        ['citizenship' => 'Indonesian'],
        ['citizenship' => 'Malaysian'],
        ['citizenship' => 'Singaporean'],
        ['citizenship' => 'Taiwanese'],
        ['citizenship' => 'Hong Kong'],
        ['citizenship' => 'Other']
    ];

    echo json_encode(['status' => 'success', 'data' => $citizenships]);
} catch (Exception $e) {
    error_log("Citizenship API Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch citizenship options']);
}
?>