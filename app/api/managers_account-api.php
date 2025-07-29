<?php
// File: ../app/api/managers_account-api.php

require_once __DIR__ . '/../core/database.php';
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

    // Check if email, employee ID, or RFID already exists
    $checkStmt = $conn->prepare("SELECT id FROM managers 
        WHERE m_email = :email OR m_employee_id = :employeeId OR m_rfid_number = :rfidNumber");
    $checkStmt->execute([
        ':email' => $data['email'],
        ':employeeId' => $data['employeeId'],
        ':rfidNumber' => $data['rfidNumber']
    ]);

    if ($checkStmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email, Employee ID, or RFID already exists']);
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

    echo json_encode([
        'status' => $success ? 'success' : 'error',
        'message' => $success ? 'Manager added successfully. Default password is their Employee ID: ' . $data['employeeId'] : 'Failed to add manager'
    ]);
}

function updateManager($conn) {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Manager ID is missing']);
        return;
    }

    // Fetch current values
    $stmt = $conn->prepare("SELECT m_email, m_employee_id, m_rfid_number FROM managers WHERE id = :id");
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

    // Check email if changed
    if ($data['email'] !== $current['m_email']) {
        $stmt = $conn->prepare("SELECT id FROM managers WHERE m_email = :email AND id != :id");
        $stmt->execute([':email' => $data['email'], ':id' => $id]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
            return;
        }
    }

    // Check employeeId if changed
    if ($data['employeeId'] !== $current['m_employee_id']) {
        $stmt = $conn->prepare("SELECT id FROM managers WHERE m_employee_id = :employeeId AND id != :id");
        $stmt->execute([':employeeId' => $data['employeeId'], ':id' => $id]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Employee ID already exists.']);
            return;
        }
    }

    // Check RFID if changed
    if ($data['rfidNumber'] !== $current['m_rfid_number']) {
        $stmt = $conn->prepare("SELECT id FROM managers WHERE m_rfid_number = :rfidNumber AND id != :id");
        $stmt->execute([':rfidNumber' => $data['rfidNumber'], ':id' => $id]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'RFID already exists.']);
            return;
        }
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
        // Try soft delete first (if deleted_at column exists)
        $stmt = $conn->prepare("UPDATE managers SET deleted_at = NOW() WHERE id = ?");
        $stmt->bindParam(1, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Manager moved to delete history successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete manager']);
        }
    } catch (PDOException $e) {
        // Fallback to hard delete if deleted_at column doesn't exist
        $stmt = $conn->prepare("DELETE FROM managers WHERE id = ?");
        $stmt->bindParam(1, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Manager deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete manager']);
        }
    }
}

function softDeleteManager($conn) {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Manager ID is required']);
        return;
    }

    try {
        // Try soft delete first (if deleted_at column exists)
        $stmt = $conn->prepare("UPDATE managers SET deleted_at = NOW() WHERE id = ?");
        $stmt->bindParam(1, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Manager moved to delete history successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete manager']);
        }
    } catch (PDOException $e) {
        // Fallback to hard delete if deleted_at column doesn't exist
        $stmt = $conn->prepare("DELETE FROM managers WHERE id = ?");
        $stmt->bindParam(1, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Manager deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete manager']);
        }
    }
}

function getManagersTable($conn) {
    try {
        // Try to fetch non-deleted managers (if deleted_at column exists)
        $stmt = $conn->prepare("SELECT * FROM managers WHERE deleted_at IS NULL ORDER BY id DESC");
        $stmt->execute();
        $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Fallback if deleted_at column doesn't exist
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
