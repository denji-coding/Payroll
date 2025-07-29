<?php
// Test script to check manager login issues
require_once "../app/core/database.php";

$db = new Database();
$conn = $db->getConnection();

echo "<h2>Manager Login Debug Information</h2>";

// Check all managers
$stmt = $conn->prepare("SELECT id, m_email, m_employee_id, m_full_name, m_password FROM managers");
$stmt->execute();
$managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>All Managers:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Email</th><th>Employee ID</th><th>Full Name</th><th>Password Status</th></tr>";

foreach ($managers as $manager) {
    $passwordStatus = empty($manager['m_password']) ? 'EMPTY' : 'SET';
    $fullNameStatus = empty($manager['m_full_name']) ? 'EMPTY' : 'SET';
    
    echo "<tr>";
    echo "<td>{$manager['id']}</td>";
    echo "<td>{$manager['m_email']}</td>";
    echo "<td>{$manager['m_employee_id']}</td>";
    echo "<td>{$manager['m_full_name']} ({$fullNameStatus})</td>";
    echo "<td>{$passwordStatus}</td>";
    echo "</tr>";
}

echo "</table>";

// Test password verification
echo "<h3>Password Test:</h3>";
if (isset($_GET['email']) && isset($_GET['password'])) {
    $email = $_GET['email'];
    $password = $_GET['password'];
    
    $stmt = $conn->prepare("SELECT * FROM managers WHERE m_email = ?");
    $stmt->execute([$email]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($manager) {
        echo "<p><strong>Testing login for:</strong> {$email}</p>";
        echo "<p><strong>Employee ID:</strong> {$manager['m_employee_id']}</p>";
        echo "<p><strong>Password hash exists:</strong> " . (!empty($manager['m_password']) ? 'YES' : 'NO') . "</p>";
        
        if (!empty($manager['m_password'])) {
            $isValid = password_verify($password, $manager['m_password']);
            echo "<p><strong>Password verification:</strong> " . ($isValid ? 'SUCCESS' : 'FAILED') . "</p>";
            
            if (!$isValid) {
                echo "<p><strong>Try using Employee ID as password:</strong> {$manager['m_employee_id']}</p>";
            }
        } else {
            echo "<p><strong>ERROR:</strong> No password hash found for this manager!</p>";
        }
    } else {
        echo "<p><strong>ERROR:</strong> Manager not found with email: {$email}</p>";
    }
}

echo "<h3>Test Login:</h3>";
echo "<form method='GET'>";
echo "<p>Email: <input type='email' name='email' placeholder='manager@example.com'></p>";
echo "<p>Password: <input type='password' name='password' placeholder='password'></p>";
echo "<p><input type='submit' value='Test Login'></p>";
echo "</form>";

echo "<h3>Instructions:</h3>";
echo "<ul>";
echo "<li>Default password for new managers is their Employee ID</li>";
echo "<li>If a manager has empty password, they need to be recreated or password reset</li>";
echo "<li>Use the test form above to verify login credentials</li>";
echo "</ul>";
?> 