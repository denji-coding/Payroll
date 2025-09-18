<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/../core/password_reset.php';

// Temporarily disable CSRF validation for forgot password (public endpoint)

try {
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['token'] ?? $_POST['token'] ?? '';
    $code = $input['code'] ?? $_POST['code'] ?? '';
    
    if (empty($token) || empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Token and code are required']);
        exit();
    }
    
    // Find the token
    $tokenData = pr_find_token($token);
    if (!$tokenData) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
        exit();
    }
    
    // Check if token is already used
    if ($tokenData['used_at']) {
        echo json_encode(['success' => false, 'message' => 'Token has already been used']);
        exit();
    }
    
    // Check if token is expired
    if (strtotime($tokenData['expires_at']) < time()) {
        echo json_encode(['success' => false, 'message' => 'Token has expired']);
        exit();
    }
    
    // Trim whitespace from both codes for comparison
    $storedCode = trim($tokenData['verification_code']);
    $providedCode = trim($code);
    
    // Verify the code
    if ($storedCode !== $providedCode) {
        echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
        exit();
    }
    
    // Mark token as verified
    $pdo = pr_get_pdo();
    $stmt = $pdo->prepare('UPDATE password_resets SET verified_at = NOW() WHERE token = ?');
    $success = $stmt->execute([$token]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Code verified successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to verify code']);
    }
    
} catch (Exception $e) {
    error_log("Verify password reset error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while verifying the code']);
}
?>
