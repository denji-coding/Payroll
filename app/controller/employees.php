
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// $admin_id = $_SESSION['admin_id'] ?? null;

// if (!$admin_id) {
//     // If it's an API request, return JSON
//     if (isset($_GET['id'])) {
//         header('Content-Type: application/json');
//         echo json_encode(['error' => 'Unauthorized admin access']);
//         exit;
//     } else {
//         // Show custom 403 page for browser access
//         http_response_code(403);
//          require_once '../app/Error/unauthorized.php'; // Adjust the path
//         exit;
//     }
// }

require_once '../app/core/database.php'; // Include your database class

// Create a new database connection instance
$db = new Database();
$conn = $db->getConnection();


// Check if this is a delete request
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    header('Content-Type: application/json');

    $employee_id = filter_var($_POST['employee_id'] ?? null, FILTER_VALIDATE_INT);

    if (!$employee_id) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'title' => 'Missing Required Field',
            'message' => 'Employee Id is required.',
            'icon' => 'warning'
        ]);
        exit;
    }

    try {
        // Delete logic here (using your existing delete code)
        $check = $conn->prepare("SELECT id FROM employees WHERE id = :id AND deleted_at IS NULL");
        $check->execute(['id' => $employee_id]);

        if ($check->rowCount() === 0) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Not Found',
                'message' => 'Employee not found.',
                'icon' => 'warning'
            ]);
            exit;
        }

        $conn->beginTransaction();
        $stmt = $conn->prepare("UPDATE employees SET deleted_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $employee_id]);
        $conn->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Employee deleted successfully.',
            'icon' => 'success'
        ]);
    } catch (PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'title' => 'Database Error',
            'message' => 'Failed to delete employee: ' . $e->getMessage(),
            'icon' => 'error'
        ]);
    }
    exit; // Important! Stop script here for delete
}



// Initialize the Employees model (if needed, pass the connection)
$employeesModel = new Employees($conn);


function checkForDuplicates($inputData, $conn, $currentEmployeeNo = null) {
    $fields = [
        'employee_no'      => 'employeeId',
        'rfid_number'      => 'rfidNumber',
        'contact_number'   => 'contactNumber',
        'email'            => 'email',
        'sss_number'       => 'sssNumber',
        'pagibig_number'   => 'pagibigNumber',
        'philhealth_number'=> 'philhealthNumber'
    ];

    $duplicates = [];

    foreach ($fields as $column => $inputKey) {
        // Skip if currentEmployeeNo is null (insert operation)
        if ($currentEmployeeNo) {
            $originalStmt = $conn->prepare("SELECT $column FROM employees WHERE employee_no = :employeeId");
            $originalStmt->bindParam(':employeeId', $currentEmployeeNo);
            $originalStmt->execute();
            $originalValue = $originalStmt->fetchColumn();

            // Normalize before comparing
            $originalValueNorm = trim(strtolower($originalValue));
            $inputValueNorm = trim(strtolower($inputData[$inputKey]));

            if ($originalValueNorm === $inputValueNorm) {
                continue;  // Skip duplicate check if value didn't change
            }
        }


        // Check if the value exists in another record
        $query = "SELECT COUNT(*) FROM employees WHERE $column = :value";
        if ($currentEmployeeNo) {
            $query .= " AND employee_no != :employeeId";
        }

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':value', $inputData[$inputKey]);
        if ($currentEmployeeNo) {
            $stmt->bindParam(':employeeId', $currentEmployeeNo);
        }
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            $duplicates[$inputKey] = true;
        }
    }

    return $duplicates;
}



function insertEmployee($inputData, $imagePath, $conn) {
    $stmt = $conn->prepare("
        INSERT INTO employees (
            employee_no, rfid_number, first_name, middle_name, last_name, dob, 
            place_of_birth, sex, civil_status, contact_number, email, citizenship, 
            blood_type, position, address, base_salary, sss_number, pagibig_number, philhealth_number, photo_path, branch_manager
        ) 
        VALUES (
            :employeeId, :rfidNumber, :firstName, :middleName, :lastName, :dob, 
            :placeOfBirth, :sex, :civilStatus, :contactNumber, :email, :citizenship, 
            :bloodType, :position, :address, :baseSalary, :sssNumber, :pagibigNumber, :philhealthNumber, :photo_path, :branchManager
        )
    ");

    bindParams($stmt, $inputData, $imagePath);
    return $stmt->execute();
}

function updateEmployee($inputData, $imagePath, $conn) {
    // 1. Update employee info first
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
            blood_type = :bloodType,
            position = :position,
            address = :address,
            base_salary = :baseSalary,
            sss_number = :sssNumber,
            pagibig_number = :pagibigNumber,
            philhealth_number = :philhealthNumber, 
            branch_manager = :branchManager";

    if ($imagePath !== null) {
        $sql .= ", photo_path = :photo_path";
    }

    $sql .= " WHERE employee_no = :employeeNo";


    $stmt = $conn->prepare($sql);

    // Bind params (example, bind all your fields as needed)
    $stmt->bindValue(':rfidNumber', $inputData['rfidNumber']);
    $stmt->bindValue(':firstName', $inputData['firstName']);
    $stmt->bindValue(':middleName', $inputData['middleName']);
    $stmt->bindValue(':lastName', $inputData['lastName']);
    $stmt->bindValue(':dob', $inputData['dob']);
    $stmt->bindValue(':placeOfBirth', $inputData['placeOfBirth']);
    $stmt->bindValue(':sex', $inputData['sex']);
    $stmt->bindValue(':civilStatus', $inputData['civilStatus']);
    $stmt->bindValue(':contactNumber', $inputData['contactNumber']);
    $stmt->bindValue(':email', $inputData['email']);
    $stmt->bindValue(':citizenship', $inputData['citizenship']);
    $stmt->bindValue(':bloodType', $inputData['bloodType']);
    $stmt->bindValue(':position', $inputData['position']);
    $stmt->bindValue(':address', $inputData['address']);
    $stmt->bindValue(':baseSalary', $inputData['baseSalary']);
    $stmt->bindValue(':sssNumber', $inputData['sssNumber']);
    $stmt->bindValue(':pagibigNumber', $inputData['pagibigNumber']);
    $stmt->bindValue(':philhealthNumber', $inputData['philhealthNumber']);
    $stmt->bindValue(':branchManager', $inputData['branchManager'] ?? null);
    if ($imagePath !== null) {
        $stmt->bindValue(':photo_path', $imagePath);
    }
    $stmt->bindValue(':employeeNo', $inputData['employeeId']);

    $success = $stmt->execute();

    if ($success) {
        // 2. Compose full name with middle initial
       // Compose full name with middle initial
        $middleInitial = !empty($inputData['middleName']) ? strtoupper(substr($inputData['middleName'], 0, 1)) . '. ' : '';
        $fullNameRaw = trim($inputData['firstName'] . ' ' . $middleInitial . $inputData['lastName']);

        // Capitalize first letter of each word
        $fullName = ucwords(strtolower($fullNameRaw));


        // 3. Get employee's internal ID from employees table (because schedules link by employee_id)
        $stmtId = $conn->prepare("SELECT id FROM employees WHERE employee_no = :employeeNo");
        $stmtId->execute(['employeeNo' => $inputData['employeeId']]);
        $employeeId = $stmtId->fetchColumn();

        if ($employeeId) {
            // 4. Get schedule IDs assigned to this employee from employee_schedules
            $stmtSchedules = $conn->prepare("SELECT schedule_id FROM employee_schedules WHERE employee_id = :employeeId");
            $stmtSchedules->execute(['employeeId' => $employeeId]);
            $scheduleIds = $stmtSchedules->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($scheduleIds)) {
                // 5. Update name in schedules table for all related schedules
                // Use query with IN clause
                $inQuery = implode(',', array_fill(0, count($scheduleIds), '?'));
                $updateSchedules = $conn->prepare("UPDATE schedules SET name = ? WHERE id IN ($inQuery)");
                $params = array_merge([$fullName], $scheduleIds);
                $updateSchedules->execute($params);
            }
        }
    }

    return $success;
}



function bindParams($stmt, $inputData, $imagePath) {
    $stmt->bindValue(':employeeId', $inputData['employeeId']);
    $stmt->bindValue(':rfidNumber', $inputData['rfidNumber']);
    $stmt->bindValue(':firstName', $inputData['firstName']);
    $stmt->bindValue(':middleName', $inputData['middleName']);
    $stmt->bindValue(':lastName', $inputData['lastName']);
    $stmt->bindValue(':dob', $inputData['dob']);
    $stmt->bindValue(':placeOfBirth', $inputData['placeOfBirth']);
    $stmt->bindValue(':sex', $inputData['sex']);
    $stmt->bindValue(':civilStatus', $inputData['civilStatus']);
    $stmt->bindValue(':contactNumber', $inputData['contactNumber']);
    $stmt->bindValue(':email', $inputData['email']);
    $stmt->bindValue(':citizenship', $inputData['citizenship']);
    $stmt->bindValue(':bloodType', $inputData['bloodType']);
    $stmt->bindValue(':position', $inputData['position']);
    $stmt->bindValue(':address', $inputData['address']);
    $stmt->bindValue(':baseSalary', $inputData['baseSalary']);
    $stmt->bindValue(':sssNumber', $inputData['sssNumber']);
    $stmt->bindValue(':pagibigNumber', $inputData['pagibigNumber']);
    $stmt->bindValue(':philhealthNumber', $inputData['philhealthNumber']);
    $stmt->bindValue(':branchManager', $inputData['branchManager'] ?? null);

    // Only bind :photo_path if it exists in the query
    if (strpos($stmt->queryString, ':photo_path') !== false) {
        $stmt->bindValue(':photo_path', $imagePath, PDO::PARAM_STR);
    }
}


// Process POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputData = [
        'employeeId'       => filter_var($_POST['employeeId'], FILTER_SANITIZE_STRING),
        'rfidNumber'       => filter_var($_POST['rfidNumber'], FILTER_SANITIZE_STRING),
        'firstName'        => filter_var($_POST['firstName'], FILTER_SANITIZE_STRING),
        'middleName'       => isset($_POST['middleName']) ? filter_var($_POST['middleName'], FILTER_SANITIZE_STRING) : '',
        'lastName'         => filter_var($_POST['lastName'], FILTER_SANITIZE_STRING),
        'dob'              => filter_var($_POST['dob'], FILTER_SANITIZE_STRING),
        'placeOfBirth'     => filter_var($_POST['placeOfBirth'], FILTER_SANITIZE_STRING),
        'sex'              => filter_var($_POST['sex'], FILTER_SANITIZE_STRING),
        'civilStatus'      => filter_var($_POST['civilStatus'], FILTER_SANITIZE_STRING),
        'contactNumber'    => filter_var($_POST['contactNumber'], FILTER_SANITIZE_STRING),
        'email'            => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
        'citizenship'      => filter_var($_POST['citizenship'], FILTER_SANITIZE_STRING),
        'bloodType'        => filter_var($_POST['bloodType'], FILTER_SANITIZE_STRING),
        'position'         => filter_var($_POST['position'], FILTER_SANITIZE_STRING),
        'address'          => filter_var($_POST['address'], FILTER_SANITIZE_STRING),
        'baseSalary'       => filter_var($_POST['baseSalary'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'sssNumber'        => filter_var($_POST['sssNumber'], FILTER_SANITIZE_STRING),
        'pagibigNumber'    => filter_var($_POST['pagibigNumber'], FILTER_SANITIZE_STRING),
        'philhealthNumber' => filter_var($_POST['philhealthNumber'], FILTER_SANITIZE_STRING),
        'branchManager'    => isset($_POST['branchManager']) ? filter_var($_POST['branchManager'], FILTER_SANITIZE_STRING) : null
    ];

    header('Content-Type: application/json');

    $requiredFields = [
        'employeeId', 'rfidNumber', 'firstName', 'lastName', 'dob', 'placeOfBirth',
        'sex', 'civilStatus', 'contactNumber', 'email', 'citizenship', 'position',
        'address', 'baseSalary', 'sssNumber', 'pagibigNumber', 'philhealthNumber', 'branchManager'
    ];

    foreach ($requiredFields as $field) {
        if (empty($inputData[$field])) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Missing Required Field',
                'message' => ucwords(preg_replace('/([a-z])([A-Z])/', '$1 $2', $field)) . ' is required.',
                'icon' => 'warning'
            ]);
            exit;
        }
    }

    // $inputData = $_POST;

    $imagePath = null;
    if (isset($_FILES['photo_path']) && $_FILES['photo_path']['error'] === UPLOAD_ERR_OK) {
        $imagePath = handleImageUpload($_FILES['photo_path']);
    }

    $isUpdate = isset($_POST['isUpdate']) && $_POST['isUpdate'] === '1';
    $duplicates = checkForDuplicates($inputData, $conn, $isUpdate ? $inputData['employeeId'] : null);

    if (in_array(true, $duplicates, true)) {
        $duplicateFields = [];
        foreach ($duplicates as $key => $hasDuplicate) {
            if ($hasDuplicate) {
                $duplicateFields[] = ucwords(str_replace(['No', 'Number'], [' No.', ' Number'], preg_replace('/([a-z])([A-Z])/', '$1 $2', $key)));
            }
        }

        $msg = implode(', ', $duplicateFields);
        if (count($duplicateFields) > 1) {
            $msg = substr_replace($msg, ' or', strrpos($msg, ','), 1);
        }

        echo json_encode([
            'status' => 'error',
            'title' => 'Duplicate Entry',
            'message' => $msg . ' already exists.',
            'icon' => 'error'
        ]);
        exit;
    }

    // Format validation
    if (!validateEmail($inputData['email'])) {
        echo json_encode(['status'=>'error','title'=>'Invalid Email','message'=>'Please provide a valid email address.','icon'=>'error']); exit;
    }
    if (!validatePhoneNumber($inputData['contactNumber'])) {
        echo json_encode(['status'=>'error','title'=>'Invalid Contact','message'=>'Contact number must be 11 digits.','icon'=>'error']); exit;
    }
    if (!validateSSS($inputData['sssNumber'])) {
        echo json_encode(['status'=>'error','title'=>'Invalid SSS','message'=>'SSS number must be 12 digits.','icon'=>'error']); exit;
    }
    if (!validatePagibig($inputData['pagibigNumber'])) {
        echo json_encode(['status'=>'error','title'=>'Invalid Pag-IBIG','message'=>'Pag-IBIG number must be 12 digits.','icon'=>'error']); exit;
    }
    if (!validatePhilhealth($inputData['philhealthNumber'])) {
        echo json_encode(['status'=>'error','title'=>'Invalid PhilHealth','message'=>'PhilHealth number must be 12 digits.','icon'=>'error']); exit;
    }

    // Final insert or update
    $success = $isUpdate
        ? updateEmployee($inputData, $imagePath, $conn)
        : insertEmployee($inputData, $imagePath, $conn);

    header('Content-Type: application/json');

    if ($success) {
        echo json_encode([
            'status' => 'success',
            'message' => $isUpdate ? 'Employee updated successfully.' : 'Employee added successfully.'
        ]);
    } else {
        $errorInfo = $conn->errorInfo();
        echo json_encode([
            'status' => 'error',
            'title' => 'Database Error',
            'message' => $isUpdate ? 'Failed to update employee.' : 'Failed to add employee.',
            'icon' => 'error',
            'debug' => $errorInfo // Add this temporarily
        ]);
    exit;
    }

    exit;
}


if (isset($_GET['payroll'], $_GET['id']) && $_GET['payroll'] === 'employees') {
    $employeeId = trim($_GET['id']);

    try {
        // Modified query to join with managers table
        $stmt = $conn->prepare("SELECT e.*, m.name AS manager_name, m.branch AS manager_branch
        FROM employees e
        LEFT JOIN managers m ON e.branch_manager = m.id
        WHERE e.employee_no = ?");
        $stmt->execute([$employeeId]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');

        if ($employee) {
            // Format the branch_manager field
            if ($employee['branch_manager'] && $employee['manager_name']) {
                $employee['branch_manager_display'] = $employee['manager_name'] . ' - ' . $employee['manager_branch'];
            } else {
                $employee['branch_manager_display'] = 'Not assigned';
            }
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => $employee
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'data' => null,
                'message' => 'Employee not found'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        error_log($e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error occurred'
        ]);
    }

    exit;
}

// GET: Fetch employees for display
$sql = "SELECT * FROM employees WHERE deleted_at IS NULL AND approved_by_manager = 1 ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data['employees'] = $employees;


$sql = "SELECT * FROM managers ORDER BY created_at DESC";
$stmt = $conn ->prepare($sql);
$stmt->execute();
$managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $managers['managers'] = $managers;

$lastManager = null;

$lastManagerQuery = $conn->query("SELECT * FROM managers ORDER BY id DESC LIMIT 1");
if ($lastManagerQuery && $lastManagerQuery->rowCount() > 0) {
    $lastManager = $lastManagerQuery->fetch(PDO::FETCH_ASSOC);
}


// View (render the employees page view)
require views_path("auth/employees");

// === Helper Functions ===

function handleImageUpload($image)
{
    $imagePath = null;
    if (isset($image) && $image['error'] === 0) {
        $imagePath = 'upload/' . basename($image['name']);
        move_uploaded_file($image['tmp_name'], $imagePath);
    }
    return $imagePath;
}


function formatValidationMessage($fields)
{
    return implode("\n• ", [''] + $fields); // Adds bullet points
}

function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhoneNumber($contactNumber)
{
    return preg_match('/^[0-9]{11}$/', $contactNumber);
}

function validateSSS($sssNumber)
{
    return preg_match('/^[0-9]{12}$/', $sssNumber);
}

function validatePagibig($pagibigNumber)
{
    return preg_match('/^[0-9]{12}$/', $pagibigNumber);
}

function validatePhilhealth($philhealthNumber)
{
    return preg_match('/^[0-9]{12}$/', $philhealthNumber);
}
?>
