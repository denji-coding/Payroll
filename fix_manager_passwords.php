<?php
// Script to fix manager passwords
require_once "../app/core/database.php";

$db = new Database();
$conn = $db->getConnection();

echo "<h2>Fix Manager Passwords</h2>";

// Find managers with empty passwords
$stmt = $conn->prepare("SELECT id, m_email, m_employee_id, m_full_name FROM managers WHERE m_password IS NULL OR m_password = ''");
$stmt->execute();
$managersWithEmptyPasswords = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($managersWithEmptyPasswords)) {
    echo "<p><strong>✅ All managers have passwords set!</strong></p>";
} else {
    echo "<h3>Managers with Empty Passwords:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Email</th><th>Employee ID</th><th>Full Name</th><th>Action</th></tr>";
    
    foreach ($managersWithEmptyPasswords as $manager) {
        echo "<tr>";
        echo "<td>{$manager['id']}</td>";
        echo "<td>{$manager['m_email']}</td>";
        echo "<td>{$manager['m_employee_id']}</td>";
        echo "<td>{$manager['m_full_name']}</td>";
        echo "<td><a href='?fix={$manager['id']}'>Fix Password</a></td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Fix specific manager password
if (isset($_GET['fix'])) {
    $managerId = $_GET['fix'];
    
    $stmt = $conn->prepare("SELECT m_employee_id, m_email FROM managers WHERE id = ?");
    $stmt->execute([$managerId]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($manager) {
        $employeeId = $manager['m_employee_id'];
        $hashedPassword = password_hash($employeeId, PASSWORD_DEFAULT);
        
        $updateStmt = $conn->prepare("UPDATE managers SET m_password = ? WHERE id = ?");
        $success = $updateStmt->execute([$hashedPassword, $managerId]);
        
        if ($success) {
            echo "<p><strong>✅ Password fixed for manager ID {$managerId}</strong></p>";
            echo "<p>Email: {$manager['m_email']}</p>";
            echo "<p>Default password set to: {$employeeId}</p>";
        } else {
            echo "<p><strong>❌ Failed to fix password for manager ID {$managerId}</strong></p>";
        }
    } else {
        echo "<p><strong>❌ Manager not found with ID {$managerId}</strong></p>";
    }
}

// Fix all empty passwords at once
if (isset($_GET['fixall'])) {
    $stmt = $conn->prepare("SELECT id, m_employee_id FROM managers WHERE m_password IS NULL OR m_password = ''");
    $stmt->execute();
    $managersToFix = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $fixedCount = 0;
    foreach ($managersToFix as $manager) {
        $employeeId = $manager['m_employee_id'];
        $hashedPassword = password_hash($employeeId, PASSWORD_DEFAULT);
        
        $updateStmt = $conn->prepare("UPDATE managers SET m_password = ? WHERE id = ?");
        if ($updateStmt->execute([$hashedPassword, $manager['id']])) {
            $fixedCount++;
        }
    }
    
    echo "<p><strong>✅ Fixed passwords for {$fixedCount} managers</strong></p>";
    echo "<p>All managers now have their Employee ID as their default password.</p>";
}

echo "<h3>Actions:</h3>";
echo "<ul>";
echo "<li><a href='?fixall=1'>Fix All Empty Passwords</a></li>";
echo "<li><a href='test_manager_login.php'>Test Login</a></li>";
echo "</ul>";

echo "<h3>Instructions:</h3>";
echo "<ul>";
echo "<li>Click 'Fix All Empty Passwords' to set default passwords for all managers</li>";
echo "<li>Default password will be their Employee ID</li>";
echo "<li>Use the test login page to verify credentials</li>";
echo "</ul>";
?> 