<?php
// File: ../app/api/managers_account-api.php

require_once __DIR__ . '/../core/database.php';
$db = new Database();
$conn = $db->getConnection();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$override = $_POST['_method'] ?? ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '');

if ($method === 'POST') {
    if (strtoupper($override) === 'PUT' || (!empty($_POST['id']) && is_numeric($_POST['id']))) {
        updateManager($conn);
    } else {
        addManager($conn);
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
        m_employee_id, m_branch, m_position, m_rfid_number, m_first_name, m_middle_name, m_last_name, m_dob,
        m_place_of_birth, m_sex, m_civil_status, m_contact_number, m_email, m_citizenship, m_blood_type,
        m_address, m_base_salary, m_sss_number, m_pagibig_number, m_philhealth_number, m_photo_path, m_password
    ) VALUES (
        :employeeId, :branchManager, :position, :rfidNumber, :firstName, :middleName, :lastName, :dob,
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
        'message' => $success ? 'Manager added successfully' : 'Failed to add manager'
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

    $photo = $_POST['existingPhoto'] ?? '';
    if (!empty($_FILES['photo_path']['name'])) {
        $uploadDir = __DIR__ . '/../../public/upload';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = pathinfo($_FILES['photo_path']['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid('emp_', true) . '.' . strtolower($ext);
        $targetPath = $uploadDir . '/' . $uniqueName;

        if (move_uploaded_file($_FILES['photo_path']['tmp_name'], $targetPath)) {
            $photo = 'upload/' . $uniqueName;
        }
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

    $stmt = $conn->prepare("DELETE FROM managers WHERE id = ?");
    $stmt->bindParam(1, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Manager deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete manager']);
    }
}
