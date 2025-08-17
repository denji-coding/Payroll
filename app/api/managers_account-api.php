<?php
// File: ../app/api/managers_account-api.php

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
        softDeleteManager($conn);
    } elseif (strtoupper($override) === 'PUT' || (!empty($_POST['id']) && is_numeric($_POST['id']))) {
        updateManager($conn);
    } else {
        addManager($conn);
    }
} elseif ($method === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'get_managers') {
        getManagersTable($conn);
    } elseif (isset($_GET['action']) && $_GET['action'] === 'get_manager') {
        getManager($conn, $_GET['id'] ?? null);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid GET request']);
    }
} elseif ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    deleteManager($conn, $_DELETE['id'] ?? null);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}


function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function addManager($conn) {
    // 🚫 Prevent accidental add during update
    if (!empty($_POST['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request: Cannot add with existing ID']);
        return;
    }

    $fields = [
        'employeeId', 'branchManager', 'position', 'rfidNumber', 'firstName', 'middleName', 'lastName', 'dob',
        'placeOfBirth', 'sex', 'civilStatus', 'contactNumber', 'email', 'citizenship', 'bloodType',
        'address', 'baseSalary', 'sssNumber', 'pagibigNumber', 'philhealthNumber'
    ];

    $data = [];
    foreach ($fields as $field) {
        $data[$field] = sanitize($_POST[$field] ?? '');
    }

    // Check for duplicates
    $duplicateErrors = [];
    
    // Check email
    $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_email = :email AND deleted_at IS NULL");
    $stmt->execute([':email' => $data['email']]);
    $duplicate = $stmt->fetch();
    if ($duplicate) {
        $duplicateErrors[] = "Email already exists.";
    }

    // Check employee ID
    $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_employee_id = :employeeId AND deleted_at IS NULL");
    $stmt->execute([':employeeId' => $data['employeeId']]);
    $duplicate = $stmt->fetch();
    if ($duplicate) {
        $duplicateErrors[] = "Employee ID already exists.";
    }

    // Check RFID
    $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_rfid_number = :rfidNumber AND deleted_at IS NULL");
    $stmt->execute([':rfidNumber' => $data['rfidNumber']]);
    $duplicate = $stmt->fetch();
    if ($duplicate) {
        $duplicateErrors[] = "RFID already exists.";
    }

    // Check phone number
    if (!empty($data['contactNumber'])) {
        $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_contact_number = :contactNumber AND deleted_at IS NULL");
        $stmt->execute([':contactNumber' => $data['contactNumber']]);
        $duplicate = $stmt->fetch();
        if ($duplicate) {
            $duplicateErrors[] = "Phone number already exists.";
        }
    }

    // Check SSS number
    if (!empty($data['sssNumber'])) {
        $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_sss_number = :sssNumber AND deleted_at IS NULL");
        $stmt->execute([':sssNumber' => $data['sssNumber']]);
        $duplicate = $stmt->fetch();
        if ($duplicate) {
            $duplicateErrors[] = "SSS number already exists with manager: " . ucwords(strtolower($duplicate['m_first_name'])) . " " . ucwords(strtolower($duplicate['m_last_name']));
        }
    }

    // Check Pag-IBIG number
    if (!empty($data['pagibigNumber'])) {
        $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_pagibig_number = :pagibigNumber AND deleted_at IS NULL");
        $stmt->execute([':pagibigNumber' => $data['pagibigNumber']]);
        $duplicate = $stmt->fetch();
        if ($duplicate) {
            $duplicateErrors[] = "Pag-IBIG number already exists with manager: " . ucwords(strtolower($duplicate['m_first_name'])) . " " . ucwords(strtolower($duplicate['m_last_name']));
        }
    }

    // Check Philhealth number
    if (!empty($data['philhealthNumber'])) {
        $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_philhealth_number = :philhealthNumber AND deleted_at IS NULL");
        $stmt->execute([':philhealthNumber' => $data['philhealthNumber']]);
        $duplicate = $stmt->fetch();
        if ($duplicate) {
            $duplicateErrors[] = "Philhealth number already exists with manager: " . ucwords(strtolower($duplicate['m_first_name'])) . " " . ucwords(strtolower($duplicate['m_last_name']));
        }
    }

    // If there are duplicate errors, return them all
    if (!empty($duplicateErrors)) {
        echo json_encode(['status' => 'error', 'message' => implode('. ', $duplicateErrors)]);
        return;
    }

    // Create full name
    $fullName = trim($data['firstName'] . ' ' . $data['middleName'] . ' ' . $data['lastName']);
    $fullName = preg_replace('/\s+/', ' ', $fullName); // Remove extra spaces

    // Hash the employee ID as the default password
    $hashedPassword = password_hash($data['employeeId'], PASSWORD_DEFAULT);
    $photo = '';

    if (!empty($_FILES['photo_path']['name'])) {
        $uploadDir = __DIR__ . '/../../public/upload';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $originalName = $_FILES['photo_path']['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $uniqueName = uniqid('emp_', true) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $uniqueName;

        if (move_uploaded_file($_FILES['photo_path']['tmp_name'], $targetPath)) {
            $photo = "upload/" . $uniqueName;
        }
    }

    $sql = "INSERT INTO managers (
        m_employee_id, m_branch, m_position, m_rfid_number, m_first_name, m_middle_name, m_last_name, m_full_name, m_dob,
        m_place_of_birth, m_sex, m_civil_status, m_contact_number, m_email, m_citizenship, m_blood_type,
        m_address, m_base_salary, m_sss_number, m_pagibig_number, m_philhealth_number, m_photo_path, m_password
    ) VALUES (
        :employeeId, :branchManager, :position, :rfidNumber, :firstName, :middleName, :lastName, :fullName, :dob,
        :placeOfBirth, :sex, :civilStatus, :contactNumber, :email, :citizenship, :bloodType,
        :address, :baseSalary, :sssNumber, :pagibigNumber, :philhealthNumber, :photo, :password
    )";

    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        ':employeeId' => $data['employeeId'],
        ':branchManager' => $data['branchManager'],
        ':position' => $data['position'],
        ':rfidNumber' => $data['rfidNumber'],
        ':firstName' => $data['firstName'],
        ':middleName' => $data['middleName'],
        ':lastName' => $data['lastName'],
        ':fullName' => $fullName,
        ':dob' => $data['dob'],
        ':placeOfBirth' => $data['placeOfBirth'],
        ':sex' => $data['sex'],
        ':civilStatus' => $data['civilStatus'],
        ':contactNumber' => $data['contactNumber'],
        ':email' => $data['email'],
        ':citizenship' => $data['citizenship'],
        ':bloodType' => $data['bloodType'],
        ':address' => $data['address'],
        ':baseSalary' => $data['baseSalary'],
        ':sssNumber' => $data['sssNumber'],
        ':pagibigNumber' => $data['pagibigNumber'],
        ':philhealthNumber' => $data['philhealthNumber'],
        ':photo' => $photo,
        ':password' => $hashedPassword
    ]);

    if ($success) {
        // Send welcome email to the new manager
        if (!empty($data['email'])) {
            try {
                $firstName = ucwords(strtolower($data['firstName']));
                $middleInitial = !empty($data['middleName']) ? strtoupper(substr($data['middleName'], 0, 1)) . '.' : '';
                $lastName = ucwords(strtolower($data['lastName']));
                $fullName = trim("$firstName $middleInitial $lastName");

                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->SMTPAuth   = true;
                $mail->Host       = 'mail.smtp2go.com';
                $mail->Username   = 'nabesis.roy@dnsc.edu.ph';
                $mail->Password   = 'pGdu8SqFpeLnVp2Y';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('noreply@migrantsventurecorp.ip-ddns.com', 'Migrants Venture Corporation');
                $mail->addReplyTo('support@migrantsventurecorp.ip-ddns.com', 'Support Team');
                $mail->addAddress($data['email'], $fullName);

                $mail->isHTML(true);
                $mail->Subject = 'Manager Account Created - Migrants Venture Corporation';
                $mail->Body = "
                    <p>Hi {$firstName},</p>
                    <p>Your <strong>manager account</strong> has been <strong>created successfully</strong>.</p>
                    <p>You may now access the <strong>manager portal</strong> using the link below:</p>
                    <p>
                        <a href='http://migrantsventurecorporation.atwebpages.com/mvcPayroll/public/index.php?payroll=login1&type=manager' target='_blank'>
                            Link: http://migrantsventurecorporation.atwebpages.com/mvcPayroll/public/index.php?payroll=login1&type=manager
                        </a>
                    </p>
                    <p><strong>Login Credentials:</strong></p>
                    <ul>
                        <li>Email: {$data['email']}</li>
                        <li>Password: {$data['employeeId']}</li>
                    </ul>
                    <br>
                    <p>– Migrants Venture Corporation</p>
                ";

                $mail->AltBody = "Hi {$firstName},\n
                Your manager account has been created by HR successfully.\n
                Access the manager portal here: http://migrantsventurecorporation.atwebpages.com/mvcPayroll/public/index.php?payroll=login1&type=manager\n
                Login Credentials:\n
                Email: {$data['email']}\n
                Password: {$data['employeeId']}\n
                – Migrants Venture Corporation";

                $mail->send();
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Manager added successfully and welcome email sent. Default password is their Employee ID: ' . $data['employeeId']
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Manager added successfully, but welcome email failed: ' . $mail->ErrorInfo . '. Default password is their Employee ID: ' . $data['employeeId']
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Manager added successfully. Default password is their Employee ID: ' . $data['employeeId'] . '. No email sent (no email address provided).'
            ]);
        }
    } else {
    echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add manager'
    ]);
    }
}

function updateManager($conn) {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Manager ID is missing']);
        return;
    }

    // Fetch current values
    $stmt = $conn->prepare("SELECT m_email, m_employee_id, m_rfid_number, m_contact_number, m_sss_number, m_pagibig_number, m_philhealth_number FROM managers WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        echo json_encode(['status' => 'error', 'message' => 'Manager not found']);
        return;
    }

    $fields = [
        'employeeId', 'branchManager', 'position', 'rfidNumber', 'firstName', 'middleName', 'lastName', 'dob',
        'placeOfBirth', 'sex', 'civilStatus', 'contactNumber', 'email', 'citizenship', 'bloodType',
        'address', 'baseSalary', 'sssNumber', 'pagibigNumber', 'philhealthNumber'
    ];

    $data = [];
    foreach ($fields as $field) {
        $data[$field] = sanitize($_POST[$field] ?? '');
    }

    // Check for duplicates (excluding current manager)
    $duplicateErrors = [];

    // Check email if changed
    if ($data['email'] !== $current['m_email']) {
        $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_email = :email AND id != :id AND deleted_at IS NULL");
        $stmt->execute([':email' => $data['email'], ':id' => $id]);
        $duplicate = $stmt->fetch();
        if ($duplicate) {
            $duplicateErrors[] = "Email already exists.";
        }
    }

    // Check employeeId if changed
    if ($data['employeeId'] !== $current['m_employee_id']) {
        $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_employee_id = :employeeId AND id != :id AND deleted_at IS NULL");
        $stmt->execute([':employeeId' => $data['employeeId'], ':id' => $id]);
        $duplicate = $stmt->fetch();
        if ($duplicate) {
            $duplicateErrors[] = "Employee ID already exists.";
        }
    }

    // Check RFID if changed
    if ($data['rfidNumber'] !== $current['m_rfid_number']) {
        $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_rfid_number = :rfidNumber AND id != :id AND deleted_at IS NULL");
        $stmt->execute([':rfidNumber' => $data['rfidNumber'], ':id' => $id]);
        $duplicate = $stmt->fetch();
        if ($duplicate) {
            $duplicateErrors[] = "RFID already exists.";
        }
    }

    // Check phone number if changed
    if ($data['contactNumber'] !== $current['m_contact_number']) {
        $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_contact_number = :contactNumber AND id != :id AND deleted_at IS NULL");
        $stmt->execute([':contactNumber' => $data['contactNumber'], ':id' => $id]);
        $duplicate = $stmt->fetch();
        if ($duplicate) {
            $duplicateErrors[] = "Phone number already exists.";
        }
    }

    // Check SSS number if changed
    if ($data['sssNumber'] !== $current['m_sss_number']) {
        $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_sss_number = :sssNumber AND id != :id AND deleted_at IS NULL");
        $stmt->execute([':sssNumber' => $data['sssNumber'], ':id' => $id]);
        $duplicate = $stmt->fetch();
        if ($duplicate) {
            $duplicateErrors[] = "SSS number already exists.";
        }
    }

    // Check Pag-IBIG number if changed
    if ($data['pagibigNumber'] !== $current['m_pagibig_number']) {
        $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_pagibig_number = :pagibigNumber AND id != :id AND deleted_at IS NULL");
        $stmt->execute([':pagibigNumber' => $data['pagibigNumber'], ':id' => $id]);
        $duplicate = $stmt->fetch();
        if ($duplicate) {
            $duplicateErrors[] = "Pag-IBIG number already exists.";
        }
    }

    // Check Philhealth number if changed
    if ($data['philhealthNumber'] !== $current['m_philhealth_number']) {
        $stmt = $conn->prepare("SELECT id, m_first_name, m_last_name FROM managers WHERE m_philhealth_number = :philhealthNumber AND id != :id AND deleted_at IS NULL");
        $stmt->execute([':philhealthNumber' => $data['philhealthNumber'], ':id' => $id]);
        $duplicate = $stmt->fetch();
        if ($duplicate) {
            $duplicateErrors[] = "Philhealth number already exists.";
        }
    }

    // If there are duplicate errors, return them all
    if (!empty($duplicateErrors)) {
        echo json_encode(['status' => 'error', 'message' => implode('. ', $duplicateErrors)]);
        return;
    }

    // Handle photo upload - preserve existing photo if no new one is uploaded
    $photo = $_POST['existingPhoto'] ?? '';
    
    if (!empty($_FILES['photo_path']['name'])) {
        $uploadDir = __DIR__ . '/../../public/upload';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = pathinfo($_FILES['photo_path']['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid('emp_', true) . '.' . strtolower($ext);
        $targetPath = $uploadDir . '/' . $uniqueName;

        // Get the uploaded file's content hash
        $uploadedFileHash = md5_file($_FILES['photo_path']['tmp_name']);

        // Check if any existing manager has the same image content
        $stmt = $conn->prepare("SELECT id, m_photo_path FROM managers WHERE id != ? AND m_photo_path IS NOT NULL AND m_photo_path != ''");
        $stmt->execute([$id]);
        $existingManagers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($existingManagers as $manager) {
            $existingPhotoPath = __DIR__ . '/../../public/' . $manager['m_photo_path'];
            if (file_exists($existingPhotoPath)) {
                $existingFileHash = md5_file($existingPhotoPath);
                if ($uploadedFileHash === $existingFileHash) {
                    echo json_encode(['status' => 'error', 'message' => 'This image is already used by another manager. Please upload a different photo.']);
                    return;
                }
            }
        }

        if (move_uploaded_file($_FILES['photo_path']['tmp_name'], $targetPath)) {
            $photo = 'upload/' . $uniqueName;
        }
    } else {
        // If no new photo is uploaded, keep the existing photo
        // The $photo variable already contains the existing photo path from $_POST['existingPhoto']
    }

    $sql = "UPDATE managers SET
        m_employee_id = :employeeId,
        m_branch = :branchManager,
        m_position = :position,
        m_rfid_number = :rfidNumber,
        m_first_name = :firstName,
        m_middle_name = :middleName,
        m_last_name = :lastName,
        m_dob = :dob,
        m_place_of_birth = :placeOfBirth,
        m_sex = :sex,
        m_civil_status = :civilStatus,
        m_contact_number = :contactNumber,
        m_email = :email,
        m_citizenship = :citizenship,
        m_blood_type = :bloodType,
        m_address = :address,
        m_base_salary = :baseSalary,
        m_sss_number = :sssNumber,
        m_pagibig_number = :pagibigNumber,
        m_philhealth_number = :philhealthNumber,
        m_photo_path = :photo,
        m_updated_at = NOW()
        WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        ':employeeId' => $data['employeeId'],
        ':branchManager' => $data['branchManager'],
        ':position' => $data['position'],
        ':rfidNumber' => $data['rfidNumber'],
        ':firstName' => $data['firstName'],
        ':middleName' => $data['middleName'],
        ':lastName' => $data['lastName'],
        ':dob' => $data['dob'],
        ':placeOfBirth' => $data['placeOfBirth'],
        ':sex' => $data['sex'],
        ':civilStatus' => $data['civilStatus'],
        ':contactNumber' => $data['contactNumber'],
        ':email' => $data['email'],
        ':citizenship' => $data['citizenship'],
        ':bloodType' => $data['bloodType'],
        ':address' => $data['address'],
        ':baseSalary' => $data['baseSalary'],
        ':sssNumber' => $data['sssNumber'],
        ':pagibigNumber' => $data['pagibigNumber'],
        ':philhealthNumber' => $data['philhealthNumber'],
        ':photo' => $photo,
        ':id' => $id
    ]);

    echo json_encode([
        'status' => $success ? 'success' : 'error',
        'message' => $success ? 'Manager updated successfully.' : 'Failed to update manager.'
    ]);
}

function deleteManager($conn, $id) {
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Manager ID is required']);
        return;
    }

    try {
        // Add deleted_at column if it doesn't exist
        $conn->exec("ALTER TABLE managers ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL");
        
        // Perform soft delete
        $stmt = $conn->prepare("UPDATE managers SET deleted_at = NOW() WHERE id = ?");
        $stmt->bindParam(1, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Manager moved to delete history successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete manager']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function softDeleteManager($conn) {
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Manager ID is required']);
        return;
    }

    try {
        // Add deleted_at column if it doesn't exist
        $conn->exec("ALTER TABLE managers ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL");
        
        // Check if manager exists before soft delete
        $checkStmt = $conn->prepare("SELECT id FROM managers WHERE id = ?");
        $checkStmt->bindParam(1, $id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Manager not found']);
            return;
        }
        
        // Perform soft delete
        $stmt = $conn->prepare("UPDATE managers SET deleted_at = NOW() WHERE id = ?");
        $stmt->bindParam(1, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Manager moved to delete history successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete manager']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getManager($conn, $id) {
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Manager ID is required']);
        return;
    }

    try {
        // Add deleted_at column if it doesn't exist
        $conn->exec("ALTER TABLE managers ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL");
        
        $stmt = $conn->prepare("SELECT * FROM managers WHERE id = ? AND deleted_at IS NULL");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $manager = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($manager) {
            echo json_encode(['status' => 'success', 'manager' => $manager]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Manager not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getManagersTable($conn) {
    try {
        // Add deleted_at column if it doesn't exist
        $conn->exec("ALTER TABLE managers ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL");
        

        
        // Fetch only non-deleted managers
        $stmt = $conn->prepare("SELECT * FROM managers WHERE deleted_at IS NULL ORDER BY id DESC");
        $stmt->execute();
        $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Fallback if there's an error
        $stmt = $conn->prepare("SELECT * FROM managers ORDER BY id DESC");
        $stmt->execute();
        $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $html = '';
    $count = 1;

    if (!empty($managers)) {
        foreach ($managers as $manager) {
            $html .= '<tr class="fade-in-slide transition-colors hover:bg-[#f2f8f2] even:bg-[#cde4cd]">';
            $html .= '<td class="p-3 align-middle font-medium">' . $count++ . '</td>';
            
            // Photo
            $html .= '<td class="p-3 align-middle font-medium">';
            $html .= '<div class="flex items-center space-x-2">';
            $html .= '<span class="relative flex shrink-0 overflow-hidden rounded-full h-12 w-12">';
            if (!empty($manager['m_photo_path'])) {
                $html .= '<img class="aspect-square h-full w-full object-cover" src="../public/' . htmlspecialchars($manager['m_photo_path']) . '" alt="Manager Photo">';
            } else {
                $defaultImage = ($manager['m_sex'] === 'Female') 
                    ? '../public/assets/image/default_women.png'
                    : '../public/assets/image/default_men.png';
                $html .= '<img class="aspect-square h-full w-full" src="' . $defaultImage . '" alt="Default Photo">';
            }
            $html .= '</span>';
            $html .= '</div>';
            $html .= '</td>';
            
            // Fullname
            $html .= '<td class="p-3 align-middle font-medium">';
            $html .= ucwords(strtolower($manager['m_first_name'])) . ' ';
            if (!empty($manager['m_middle_name'])) {
                $html .= strtoupper(substr($manager['m_middle_name'], 0, 1)) . '. ';
            }
            $html .= ucwords(strtolower($manager['m_last_name']));
            $html .= '</td>';
            
            // Employee ID
            $html .= '<td class="p-3 align-middle">' . htmlspecialchars($manager['m_employee_id']) . '</td>';
            
            // RFID
            $html .= '<td class="p-3 align-middle">' . htmlspecialchars($manager['m_rfid_number']) . '</td>';
            
            // Position
            $position = $manager['m_position'] ?? '';
            $bgColor = '';
            switch ($position) {
                case 'Manager':
                    $bgColor = 'bg-green-600 text-white';
                    break;
                case 'Human Resources':
                    $bgColor = 'bg-blue-600 text-white';
                    break;
                case 'Staff':
                    $bgColor = 'bg-yellow-600 text-white';
                    break;
                case 'Driver':
                    $bgColor = 'bg-red-600 text-white';
                    break;
                default:
                    $bgColor = 'bg-gray-500 text-white';
                    break;
            }
            $html .= '<td class="p-3 align-middle text-center">';
            $html .= '<div class="inline-flex items-center rounded-full border border-transparent ' . $bgColor . ' px-2.5 py-0.5 text-xs font-semibold">';
            $html .= htmlspecialchars($position);
            $html .= '</div>';
            $html .= '</td>';
            
            // Actions
            $html .= '<td class="p-3 align-middle text-right">';
            $html .= '<div class="flex gap-2">';
            $html .= '<button type="button" class="viewManagerBtn" data-manager=\'' . json_encode($manager, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . '\' data-manager-id="' . $manager['id'] . '" data-bs-toggle="modal" data-bs-target="#viewManagerModal">';
            $html .= '<i class="bi bi-eye"></i>';
            $html .= '</button>';
            $html .= '<div class="dropdown relative inline-block">';
            $html .= '<button class="dropdown-toggle-btn inline-flex h-8 w-8 items-center justify-center rounded-md font-medium transition duration-100 transform hover:scale-105 hover:bg-[#478547] hover:text-white" type="button" data-bs-toggle="dropdown">';
            $html .= '<i class="bi bi-person-gear text-lg"></i>';
            $html .= '</button>';
            $html .= '<ul class="dropdown-menu absolute right-0 mt-2 w-48 rounded-md shadow-md bg-white ring-1 ring-black ring-opacity-5 z-50">';
            $html .= '<li><a href="#" class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" onclick="openModal(\'viewAttendanceModal\', ' . $manager['id'] . ')"><i class="bi bi-calendar-check h-4 w-4"></i><span>View Attendance</span></a></li>';
            $html .= '<li><a href="#" class="dropdown-item flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-[#f2f8f2] hover:text-[#478547]" onclick="openModal(\'viewSlipsModal\', ' . $manager['id'] . ')"><i class="bi bi-receipt h-4 w-4"></i><span>View Slips</span></a></li>';
            $html .= '</ul>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</td>';
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="7" class="p-4 text-center italic text-gray-500 bg-[#f0fdf4]"><i class="bi bi-person-x me-2"></i> No managers found.</td></tr>';
    }

    echo json_encode(['status' => 'success', 'html' => $html]);
}
