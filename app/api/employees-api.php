<?php

// === Headers and CORS ===
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === Load Dependencies ===
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../Model/Employees.php';
require_once __DIR__ . '/../core/SecureAPIMiddleware.php';

// === Apply API Middleware ===
applyAPIMiddleware('employees');

header('Content-Type: application/json');

// === Initialize DB ===
$db = new Database();
$conn = $db->getConnection();

// Test database connection
if (!$conn) {
    error_log("Employee API - Database connection failed");
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Test if employees table exists and has the required columns
try {
    $testStmt = $conn->query("DESCRIBE employees");
    $columns = $testStmt->fetchAll(PDO::FETCH_COLUMN);
    error_log("Employee API - Available columns: " . implode(', ', $columns));
} catch (PDOException $e) {
    error_log("Employee API - Table structure error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database table error: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true);
$data = $_POST ?: $input;

// === GET: Fetch employees ===
if ($method === 'GET') {
    try {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];

            $stmt = $conn->prepare("
                SELECT 
                    e.*, 
                    CONCAT_WS(' ', m.m_first_name, m.m_middle_name, m.m_last_name) AS manager_name,
                    m.m_branch AS branch_name
                FROM employees e
                LEFT JOIN managers m ON e.branch_manager = m.id
                WHERE e.employee_no = ? AND e.deleted_at IS NULL
            ");
            $stmt->execute([$id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee) {
                echo json_encode(['status' => 'success', 'data' => $employee]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
            }
        } else {
            $stmt = $conn->prepare("
                SELECT 
                    e.*, 
                    CONCAT_WS(' ', m.m_first_name, m.m_middle_name, m.m_last_name) AS manager_name,
                    m.m_branch AS branch_name
                FROM employees e
                LEFT JOIN managers m ON e.branch_manager = m.id
                WHERE e.deleted_at IS NULL AND e.approved_by_manager = 1
                ORDER BY e.id DESC
            ");
            $stmt->execute();
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $employees]);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage(), 3, __DIR__ . '/../logs/error.log');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Server error']);
    }
    exit;
}

// === POST: Create / Update / Soft Delete ===
if ($method === 'POST') {

    // Debug: Log the received data
    error_log("Employee API - Received data: " . print_r($data, true));
    error_log("Employee API - Files: " . print_r($_FILES, true));

    // === Soft Delete Action ===
    if (isset($data['action']) && $data['action'] === 'delete') {
        $employeeId = filter_var($data['employeeId'] ?? null, FILTER_VALIDATE_INT);

        if (!$employeeId) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'icon' => 'error',
                'title' => 'Invalid Request',
                'message' => 'Valid Employee ID is required.'
            ]);
            exit;
        }

        try {
            $stmt = $conn->prepare("UPDATE employees SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$employeeId]);

            if ($stmt->rowCount() > 0) {
                // Fetch updated employee list
                $stmt = $conn->prepare("
                    SELECT 
                        e.*, 
                        m.m_first_name AS manager_first_name,
                        m.m_middle_name AS manager_middle_name,
                        m.m_last_name AS manager_last_name,
                        m.m_branch AS branch_name
                    FROM employees e
                    LEFT JOIN managers m ON e.branch_manager = m.id
                    WHERE e.deleted_at IS NULL AND e.approved_by_manager = 1
                    ORDER BY e.id DESC
                ");
                $stmt->execute();
                $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

                ob_start();
                $index = 1;
                foreach ($employees as $emp) {
                    include __DIR__ . '/../app/view/partials/employee_table_partial.php';
                }
                $tbodyHtml = ob_get_clean();

                echo json_encode([
                    'status' => 'success',
                    'icon' => 'success',
                    'title' => 'Deleted!',
                    'message' => 'Employee successfully deleted.',
                    'tbody' => $tbodyHtml
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'icon' => 'error',
                    'title' => 'Deletion Failed',
                    'message' => 'No matching employee found or already deleted.'
                ]);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, __DIR__ . '/../logs/error.log');
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'icon' => 'error',
                'title' => 'Server Error',
                'message' => 'Failed to delete employee.'
            ]);
        }
        exit;
    }

    // === Create or Update Employee ===
    $required = [
        'employeeId', 'rfidNumber', 'firstName', 'lastName', 'dob',
        'placeOfBirth', 'sex', 'civilStatus', 'contactNumber', 'email',
        'citizenship', 'position', 'address', 'baseSalary',
        'sssNumber', 'pagibigNumber', 'philhealthNumber', 'branchManager'
    ];

    // Check which required fields are missing
    $missingFields = [];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        error_log("Employee API - Missing required fields: " . implode(', ', $missingFields));
        echo json_encode([
            'status' => 'error', 
            'message' => 'Missing required fields: ' . implode(', ', $missingFields),
            'missing' => $missingFields
        ]);
        exit;
    }

    // Debug: Log the data being processed
    error_log("Employee API - Processing data for employee: " . $data['employeeId']);
    error_log("Employee API - Position: " . $data['position']);
    error_log("Employee API - Branch Manager: " . $data['branchManager']);

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
        exit;
    }

    $photoPath = null;
    if (isset($_FILES['photo_path']) && $_FILES['photo_path']['error'] === UPLOAD_ERR_OK) {
        $targetDir = __DIR__ . "/../../public/upload/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        $fileTmpPath = $_FILES['photo_path']['tmp_name'];
        $fileName = basename($_FILES['photo_path']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($fileExt, $allowedExts)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid image type']);
            exit;
        }

        $checkImage = $conn->prepare("SELECT id FROM employees WHERE photo_path = ? AND employee_no != ?");
        $checkImage->execute(["upload/" . $fileName, $data['employeeId']]);
        if ($checkImage->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'This image is already used by another employee. Please rename or upload a different photo.']);
            exit;
        }

        $newFileName = uniqid('emp_', true) . "." . $fileExt;
        $destPath = $targetDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $photoPath = "upload/" . $newFileName;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload image']);
            exit;
        }
    }

    try {
        $stmt = $conn->prepare("SELECT id FROM employees WHERE employee_no = ?");
        $stmt->execute([$data['employeeId']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $sql = "UPDATE employees SET
                rfid_number = :rfidNumber,
                first_name = :firstName,
                middle_name = :middleName,
                last_name = :lastName,
                dob = :dob,
                place_of_birth = :placeOfBirth,
                sex = :sex,
                civil_status = :civilStatus,
                contact_number = :contactNumber,
                email = :email,
                citizenship = :citizenship,
                position = :position,
                address = :address,
                base_salary = :baseSalary,
                sss_number = :sssNumber,
                pagibig_number = :pagibigNumber,
                philhealth_number = :philhealthNumber,
                branch_manager = :branchManager";

            if ($photoPath) {
                $sql .= ", photo_path = :photo_path";
            }

            $sql .= " WHERE employee_no = :employeeId";
        } else {
            $sql = "INSERT INTO employees (
                employee_no, rfid_number, first_name, middle_name, last_name, dob,
                place_of_birth, sex, civil_status, contact_number, email,
                citizenship, position, address, base_salary,
                sss_number, pagibig_number, philhealth_number, branch_manager, photo_path
            ) VALUES (
                :employeeId, :rfidNumber, :firstName, :middleName, :lastName, :dob,
                :placeOfBirth, :sex, :civilStatus, :contactNumber, :email,
                :citizenship, :position, :address, :baseSalary,
                :sssNumber, :pagibigNumber, :philhealthNumber, :branchManager, :photo_path
            )";
        }

        $stmt = $conn->prepare($sql);

        // Bind all parameters including middleName
        $placeholders = [
            'employeeId', 'rfidNumber', 'firstName', 'middleName', 'lastName',
            'dob', 'placeOfBirth', 'sex', 'civilStatus', 'contactNumber', 'email',
            'citizenship', 'position', 'address', 'baseSalary',
            'sssNumber', 'pagibigNumber', 'philhealthNumber', 'branchManager'
        ];

        foreach ($placeholders as $key) {
            $stmt->bindValue(":$key", $data[$key] ?? null);
        }

        if ($photoPath || !$existing) {
            $stmt->bindValue(":photo_path", $photoPath ?? null);
        }

        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => $existing ? 'Employee updated' : 'Employee added'
        ]);
    } catch (PDOException $e) {
        error_log("Employee API Error: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database error: ' . $e->getMessage(),
            'details' => 'Check server logs for more information'
        ]);
    }
    exit;
}

// === Fallback ===
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
exit;
