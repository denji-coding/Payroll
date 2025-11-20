<?php
// File: ../app/api/hr_account-api.php

require_once __DIR__ . '/../core/database.php';
require __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = new Database();
$conn = $db->getConnection();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$override = $_POST['_method'] ?? ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '');

if ($method === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
        softDeleteHr($conn);
    } elseif (strtoupper($override) === 'PUT' || (!empty($_POST['id']) && is_numeric($_POST['id']))) {
        updateHr($conn);
    } else {
        addHr($conn);
    }
} elseif ($method === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'get_hr') {
        getHrTable($conn);
    } elseif (isset($_GET['action']) && $_GET['action'] === 'get_hr_single') {
        getHr($conn, $_GET['id'] ?? null);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid GET request']);
    }
} elseif ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    deleteHr($conn, $_DELETE['id'] ?? null);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function addHr($conn) {
    try {
        // Validate required fields
        $requiredFields = ['employeeId', 'firstName', 'lastName', 'email', 'position', 'rfidNumber', 'dob', 'placeOfBirth', 'sex', 'civilStatus', 'contactNumber', 'citizenship', 'bloodType', 'address', 'baseSalary', 'sssNumber', 'pagibigNumber', 'philhealthNumber'];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['status' => 'error', 'message' => "Field $field is required"]);
                return;
            }
        }

        // Check if HR employee ID already exists
        $checkStmt = $conn->prepare("SELECT id FROM admins WHERE hr_employee_id = ?");
        $checkStmt->execute([sanitize($_POST['employeeId'])]);
        if ($checkStmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'HR Employee ID already exists']);
            return;
        }

        // Check if email already exists
        $checkEmailStmt = $conn->prepare("SELECT id FROM admins WHERE hr_email = ?");
        $checkEmailStmt->execute([sanitize($_POST['email'])]);
        if ($checkEmailStmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
            return;
        }

        // Check if RFID already exists across all user types (employees, managers, HR)
        if (!empty($_POST['rfidNumber'])) {
            $rfidNumber = sanitize($_POST['rfidNumber']);
            
            // Check in employees table
            $checkRfidEmployeeStmt = $conn->prepare("SELECT id, employee_no, CONCAT(first_name, ' ', last_name) as name FROM employees WHERE rfid_number = ? AND deleted_at IS NULL");
            $checkRfidEmployeeStmt->execute([$rfidNumber]);
            $rfidEmployee = $checkRfidEmployeeStmt->fetch(PDO::FETCH_ASSOC);
            if ($rfidEmployee) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'RFID number is already used by employee: ' . $rfidEmployee['name'] . ' (ID: ' . $rfidEmployee['employee_no'] . ')'
                ]);
                return;
            }
            
            // Check in managers table
            $checkRfidManagerStmt = $conn->prepare("SELECT id, m_employee_id, CONCAT(m_first_name, ' ', m_last_name) as name FROM managers WHERE m_rfid_number = ? AND deleted_at IS NULL");
            $checkRfidManagerStmt->execute([$rfidNumber]);
            $rfidManager = $checkRfidManagerStmt->fetch(PDO::FETCH_ASSOC);
            if ($rfidManager) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'RFID number is already used by manager: ' . $rfidManager['name'] . ' (ID: ' . $rfidManager['m_employee_id'] . ')'
                ]);
                return;
            }
            
            // Check in admins (HR) table
            $checkRfidHrStmt = $conn->prepare("SELECT id, hr_employee_id, CONCAT(hr_first_name, ' ', hr_last_name) as name FROM admins WHERE hr_rfid_number = ? AND deleted_at IS NULL");
            $checkRfidHrStmt->execute([$rfidNumber]);
            $rfidHr = $checkRfidHrStmt->fetch(PDO::FETCH_ASSOC);
            if ($rfidHr) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'RFID number is already used by HR: ' . $rfidHr['name'] . ' (ID: ' . $rfidHr['hr_employee_id'] . ')'
                ]);
                return;
            }
        }

        // Handle photo upload
        $photoPath = null;
        if (isset($_FILES['photo_path']) && $_FILES['photo_path']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/upload/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = pathinfo($_FILES['photo_path']['name'], PATHINFO_EXTENSION);
            $fileName = 'hr_' . uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['photo_path']['tmp_name'], $uploadPath)) {
                $photoPath = 'upload/' . $fileName;
            }
        }

        // Generate password (default: employee ID)
        $defaultPassword = password_hash(sanitize($_POST['employeeId']), PASSWORD_DEFAULT);

        // Insert HR record
        $stmt = $conn->prepare("INSERT INTO admins (
            hr_employee_id, hr_photo_path, hr_first_name, hr_middle_name, hr_last_name, 
            hr_email, hr_position, hr_rfid_number, hr_dob, hr_place_of_birth, 
            hr_sex, hr_civil_status, hr_contact_number, hr_citizenship, hr_blood_type, 
            hr_address, hr_base_salary, hr_sss_number, hr_pagibig_number, hr_philhealth_number, 
            hr_password
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            sanitize($_POST['employeeId']),
            $photoPath,
            sanitize($_POST['firstName']),
            sanitize($_POST['middleName'] ?? ''),
            sanitize($_POST['lastName']),
            sanitize($_POST['email']),
            sanitize($_POST['position']),
            sanitize($_POST['rfidNumber']),
            sanitize($_POST['dob']),
            sanitize($_POST['placeOfBirth']),
            sanitize($_POST['sex']),
            sanitize($_POST['civilStatus']),
            sanitize($_POST['contactNumber']),
            sanitize($_POST['citizenship']),
            sanitize($_POST['bloodType']),
            sanitize($_POST['address']),
            sanitize($_POST['baseSalary']),
            sanitize($_POST['sssNumber']),
            sanitize($_POST['pagibigNumber']),
            sanitize($_POST['philhealthNumber']),
            $defaultPassword
        ]);

        // Send welcome email to HR
        $hrEmail = sanitize($_POST['email']);
        $hrName = sanitize($_POST['firstName']) . ' ' . sanitize($_POST['lastName']);
        $employeeId = sanitize($_POST['employeeId']);
        $defaultPassword = sanitize($_POST['employeeId']); // Same as what was hashed
        
        sendHrWelcomeEmail($hrEmail, $hrName, $employeeId, $defaultPassword);

        echo json_encode(['status' => 'success', 'message' => 'HR added successfully! Welcome email sent to ' . $hrEmail]);
    } catch (Exception $e) {
        error_log("Add HR Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to add HR: ' . $e->getMessage()]);
    }
}

function updateHr($conn) {
    try {
        $hrId = sanitize($_POST['id']);
        if (empty($hrId)) {
            echo json_encode(['status' => 'error', 'message' => 'HR ID is required']);
            return;
        }

        // Check if HR exists
        $checkStmt = $conn->prepare("SELECT hr_photo_path, hr_rfid_number, hr_employee_id FROM admins WHERE id = ?");
        $checkStmt->execute([$hrId]);
        $existingHr = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingHr) {
            echo json_encode(['status' => 'error', 'message' => 'HR not found']);
            return;
        }

        // Check if RFID already exists across all user types (employees, managers, HR) - excluding current HR
        if (!empty($_POST['rfidNumber'])) {
            $rfidNumber = sanitize($_POST['rfidNumber']);
            $currentRfid = $existingHr['hr_rfid_number'] ?? '';
            
            // Only check if RFID is being changed
            if ($rfidNumber !== $currentRfid) {
                // Check in employees table
                $checkRfidEmployeeStmt = $conn->prepare("SELECT id, employee_no, CONCAT(first_name, ' ', last_name) as name FROM employees WHERE rfid_number = ? AND deleted_at IS NULL");
                $checkRfidEmployeeStmt->execute([$rfidNumber]);
                $rfidEmployee = $checkRfidEmployeeStmt->fetch(PDO::FETCH_ASSOC);
                if ($rfidEmployee) {
                    echo json_encode([
                        'status' => 'error', 
                        'message' => 'RFID number is already used by employee: ' . $rfidEmployee['name'] . ' (ID: ' . $rfidEmployee['employee_no'] . ')'
                    ]);
                    return;
                }
                
                // Check in managers table
                $checkRfidManagerStmt = $conn->prepare("SELECT id, m_employee_id, CONCAT(m_first_name, ' ', m_last_name) as name FROM managers WHERE m_rfid_number = ? AND deleted_at IS NULL");
                $checkRfidManagerStmt->execute([$rfidNumber]);
                $rfidManager = $checkRfidManagerStmt->fetch(PDO::FETCH_ASSOC);
                if ($rfidManager) {
                    echo json_encode([
                        'status' => 'error', 
                        'message' => 'RFID number is already used by manager: ' . $rfidManager['name'] . ' (ID: ' . $rfidManager['m_employee_id'] . ')'
                    ]);
                    return;
                }
                
                // Check in admins (HR) table - excluding current HR
                $checkRfidHrStmt = $conn->prepare("SELECT id, hr_employee_id, CONCAT(hr_first_name, ' ', hr_last_name) as name FROM admins WHERE hr_rfid_number = ? AND id != ? AND deleted_at IS NULL");
                $checkRfidHrStmt->execute([$rfidNumber, $hrId]);
                $rfidHr = $checkRfidHrStmt->fetch(PDO::FETCH_ASSOC);
                if ($rfidHr) {
                    echo json_encode([
                        'status' => 'error', 
                        'message' => 'RFID number is already used by HR: ' . $rfidHr['name'] . ' (ID: ' . $rfidHr['hr_employee_id'] . ')'
                    ]);
                    return;
                }
            }
        }

        // Handle photo upload
        $photoPath = $existingHr['hr_photo_path']; // Keep existing photo
        if (isset($_FILES['photo_path']) && $_FILES['photo_path']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/upload/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = pathinfo($_FILES['photo_path']['name'], PATHINFO_EXTENSION);
            $fileName = 'hr_' . uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['photo_path']['tmp_name'], $uploadPath)) {
                // Delete old photo if exists
                if ($photoPath && file_exists(__DIR__ . '/../../public/' . $photoPath)) {
                    unlink(__DIR__ . '/../../public/' . $photoPath);
                }
                $photoPath = 'upload/' . $fileName;
            }
        }

        // Update HR record
        $stmt = $conn->prepare("UPDATE admins SET 
            hr_photo_path = ?, hr_first_name = ?, hr_middle_name = ?, hr_last_name = ?, 
            hr_email = ?, hr_position = ?, hr_rfid_number = ?, hr_dob = ?, hr_place_of_birth = ?, 
            hr_sex = ?, hr_civil_status = ?, hr_contact_number = ?, hr_citizenship = ?, hr_blood_type = ?, 
            hr_address = ?, hr_base_salary = ?, hr_sss_number = ?, hr_pagibig_number = ?, hr_philhealth_number = ?,
            hr_updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");

        $stmt->execute([
            $photoPath,
            sanitize($_POST['firstName']),
            sanitize($_POST['middleName'] ?? ''),
            sanitize($_POST['lastName']),
            sanitize($_POST['email']),
            sanitize($_POST['position']),
            sanitize($_POST['rfidNumber']),
            sanitize($_POST['dob']),
            sanitize($_POST['placeOfBirth']),
            sanitize($_POST['sex']),
            sanitize($_POST['civilStatus']),
            sanitize($_POST['contactNumber']),
            sanitize($_POST['citizenship']),
            sanitize($_POST['bloodType']),
            sanitize($_POST['address']),
            sanitize($_POST['baseSalary']),
            sanitize($_POST['sssNumber']),
            sanitize($_POST['pagibigNumber']),
            sanitize($_POST['philhealthNumber']),
            $hrId
        ]);

        echo json_encode(['status' => 'success', 'message' => 'HR updated successfully']);
    } catch (Exception $e) {
        error_log("Update HR Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to update HR: ' . $e->getMessage()]);
    }
}

function getHrTable($conn) {
    try {
        $stmt = $conn->prepare("SELECT 
            h.*,
            CONCAT(
                UPPER(LEFT(h.hr_first_name, 1)), LOWER(SUBSTRING(h.hr_first_name FROM 2)), ' ',
                IFNULL(CONCAT(UPPER(LEFT(h.hr_middle_name, 1)), '. '), ''),
                UPPER(LEFT(h.hr_last_name, 1)), LOWER(SUBSTRING(h.hr_last_name FROM 2))
            ) AS full_name
        FROM admins h 
        WHERE h.deleted_at IS NULL 
        ORDER BY h.hr_created_at DESC");
        
        $stmt->execute();
        $hrList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'data' => $hrList]);
    } catch (Exception $e) {
        error_log("Get HR Table Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch HR data']);
    }
}

function getHr($conn, $id) {
    try {
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'HR ID is required']);
            return;
        }

        $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $hr = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$hr) {
            echo json_encode(['status' => 'error', 'message' => 'HR not found']);
            return;
        }
        
        echo json_encode(['status' => 'success', 'data' => $hr]);
    } catch (Exception $e) {
        error_log("Get HR Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch HR data']);
    }
}

function softDeleteHr($conn) {
    try {
        $hrId = sanitize($_POST['id']);
        if (empty($hrId)) {
            echo json_encode(['status' => 'error', 'message' => 'HR ID is required']);
            return;
        }

        $stmt = $conn->prepare("UPDATE admins SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$hrId]);

        echo json_encode(['status' => 'success', 'message' => 'HR moved to history successfully']);
    } catch (Exception $e) {
        error_log("Soft Delete HR Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete HR']);
    }
}

function deleteHr($conn, $id) {
    try {
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'HR ID is required']);
            return;
        }

        $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'success', 'message' => 'HR deleted permanently']);
    } catch (Exception $e) {
        error_log("Delete HR Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete HR']);
    }
}

function sendHrWelcomeEmail($email, $name, $employeeId, $password) {
    try {
        $mail = new PHPMailer(true);
        
        // Gmail SMTP Configuration
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = 'smtp.gmail.com';
        $mail->Username = 'migrantsventurecorporation@gmail.com';
        $mail->Password = 'tfop acec ukat dosw'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use STARTTLS for port 587
        $mail->Port = 587; // Use port 587 for STARTTLS
        
        // Recipients - Sender must match Gmail account
        $mail->setFrom('migrantsventurecorporation@gmail.com', 'Migrants Venture Corporation');
        $mail->addReplyTo('support@migrantsventurecorp.ip-ddns.com', 'Support Team');
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'HR Account Created - Migrants Venture Corporation';
        
        // Replace localhost with your real server URL
        $loginUrl = "http://localhost/mvcPayroll/public/index.php?payroll=login_admin";
        
        $mail->Body = "
        <p>Hi " . htmlspecialchars($name) . ",</p>
        <p>Your <strong>HR account</strong> has been <strong>created successfully</strong>.</p>
        <p>You may now log in to the portal:</p>
        <p><a href='$loginUrl' target='_blank'>$loginUrl</a></p>
        
        <p><strong>Login Credentials:</strong></p>
        <ul>
            <li>Email: " . htmlspecialchars($email) . "</li>
            <li>Password: " . htmlspecialchars($password) . "</li>
        </ul>
        <br>
        <p>– Migrants Venture Corporation</p>
        ";
        
        $mail->AltBody = "Hi $name,\nYour HR account has been created.\nLogin: $loginUrl\nEmail: $email\nPassword: $password\n– Migrants Venture Corporation";
        
        $mail->send();
        error_log("Welcome email sent successfully to: " . $email);
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        // Don't throw exception to avoid breaking the main flow
    }
}
?>
