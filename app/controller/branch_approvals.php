<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/core/database.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$db = new Database();
$pdo = $db->getConnection();

$managerId = $_SESSION['manager_id'] ?? null;
if (!$managerId) {
    die("Manager not authenticated.");
}

// Handle approve/reject POST AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    header('Content-Type: application/json');

    $id = $_POST['id'];
    $action = $_POST['action'];

    if (!in_array($action, ['approve', 'reject'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
    }

    try {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE employees SET approved_by_manager = 1 WHERE id = :id AND branch_manager = :managerId");
            $stmt->execute(['id' => $id, 'managerId' => $managerId]);

            if ($stmt->rowCount() > 0) {
                // Fetch employee data for email including employee_no
                $empStmt = $pdo->prepare("SELECT email, employee_no, first_name, middle_name, last_name FROM employees WHERE id = :id");
                $empStmt->execute(['id' => $id]);
                $employee = $empStmt->fetch();

                if ($employee && !empty($employee['email'])) {
                    try {
                        $firstName = ucwords(strtolower($employee['first_name']));
                        $middleInitial = !empty($employee['middle_name']) ? strtoupper(substr($employee['middle_name'], 0, 1)) . '.' : '';
                        $lastName = ucwords(strtolower($employee['last_name']));
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
                        $mail->addAddress($employee['email'], $fullName);

                        $mail->isHTML(true);
                        $mail->Subject = 'Approval Notification';
                        $mail->Body = "
                            <p>Hi {$firstName},</p>
                            <p>Your employee account has been <strong>approved</strong> by your manager.</p>
                            <p>You may now access the employee portal using the link below:</p>
                            <p>
                                <a href='http://migrantsventurecorporation.atwebpages.com/mvcPayroll/public/index.php?payroll=login1&type=employee' target='_blank'>
                                    Link: http://migrantsventurecorporation.atwebpages.com/mvcPayroll/public/index.php?payroll=login1&type=employee
                                </a>
                            </p>
                            <p><strong>Login Credentials:</strong></p>
                            <ul>
                                <li>Email: {$employee['email']}</li>
                                <li>Employee Number: {$employee['employee_no']}</li>
                            </ul>
                            <br>
                            <p>– Migrants Venture Corporation</p>
                        ";

                        $mail->AltBody = "Hi {$firstName},\n
                        Your employee account has been approved.\n
                        Access the portal here: http://migrantsventurecorporation.atwebpages.com/mvcPayroll/public/index.php?payroll=login1&type=employee\n
                        Login Credentials:\n
                        Email: {$employee['email']}\n
                        Password: {$employee['employee_no']}\n
                        – Migrants Venture Corporation";

                        $mail->send();
                        echo json_encode(['status' => 'success', 'message' => 'Approved and email sent.']);
                    } catch (Exception $e) {
                        echo json_encode(['status' => 'success', 'message' => 'Approved, but email failed: ' . $mail->ErrorInfo]);
                    }
                } else {
                    echo json_encode(['status' => 'success', 'message' => 'Approved, but no employee email found.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No matching employee found or already approved']);
            }

        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE employees SET approved_by_manager = -1 WHERE id = :id AND branch_manager = :managerId");
            $stmt->execute(['id' => $id, 'managerId' => $managerId]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Rejected successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No matching employee found or already rejected']);
            }
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

    exit;
}

// Normal page load: fetch employee list
try {
    $stmt = $pdo->prepare("
        SELECT *
        FROM employees
        WHERE branch_manager = :managerId AND approved_by_manager IN (0, -1)
    ");
    $stmt->execute(['managerId' => $managerId]);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format full name for each employee record
    foreach ($list as &$employee) {
        $firstName = ucfirst(strtolower($employee['first_name']));
        $middleInitial = !empty($employee['middle_name']) ? strtoupper(substr($employee['middle_name'], 0, 1)) . '.' : '';
        $lastName = ucfirst(strtolower($employee['last_name']));
        $employee['full_name'] = trim("$firstName $middleInitial $lastName");
    }
    unset($employee);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require views_path("branch/branch_approvals");
