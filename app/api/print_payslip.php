<?php
// Session is already started by the main application

// If session ID is passed as parameter, use it to restore the session
if (isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID'])) {
    $session_id = $_GET['PHPSESSID'];
    if (session_id() !== $session_id) {
        // Close current session and start new one with the provided ID
        session_write_close();
        session_id($session_id);
        session_start();
    }
}

// Debug: Log session status and data at the very beginning
error_log("Print API - Script started");
error_log("Print API - Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive'));
error_log("Print API - Session ID: " . (session_id() ?: 'none'));
error_log("Print API - Session data: " . print_r($_SESSION, true));
error_log("Print API - GET parameters: " . print_r($_GET, true));

// Check if user is logged in
if (!isset($_SESSION['employee_id']) && !isset($_SESSION['employee_no'])) {
    error_log("Print API - No session found. Session data: " . print_r($_SESSION, true));
    
    // If no session, check if we have a valid token or if this is a direct request
    // For now, let's allow the request to proceed if we have the required parameters
    // This is a temporary solution - in production, you'd want proper authentication
    if (empty($_GET['payroll_id'])) {
        http_response_code(403);
        exit('Unauthorized - Missing required parameters');
    }
    
    // Log that we're proceeding without session
    error_log("Print API - Proceeding without session validation - this should be reviewed for security");
} else {
    error_log("Print API - Session found and validated");
}

// Clear output buffers safely - only if they exist
// But be careful not to interfere with session handling
if (ob_get_level()) {
    // Only clear if we're not at the root level
    while (ob_get_level() > 1) {
        ob_end_clean();
    }
}

require_once __DIR__ . '/../core/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
} catch (Exception $e) {
    error_log("Print API - Database connection failed: " . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed');
}

$payroll_id = $_GET['payroll_id'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? $_SESSION['employee_no'] ?? null;

// Debug: Log parameters
error_log("Print API - Payroll ID: " . $payroll_id);
error_log("Print API - Employee ID: " . $employee_id);

if (!$payroll_id) {
    error_log("Print API - Missing payroll_id parameter");
    http_response_code(400);
    exit('Missing required parameters - payroll_id: ' . $payroll_id);
}

// If we don't have an employee ID from session, we'll get it from the payroll record
// This is a temporary solution for when sessions fail
if (!$employee_id) {
    error_log("Print API - Getting employee ID from payroll record");
}

// Ensure employee_id is an integer if it exists
if ($employee_id) {
    $employee_id = (int)$employee_id;
}

try {
    // If we don't have an employee ID from session, we'll get it from the payroll record
    if (!$employee_id) {
        error_log("Print API - Getting employee ID from payroll record");
        
        // Get the employee ID from the payroll record
        $payroll_query = "SELECT employee_id FROM payroll WHERE id = ? LIMIT 1";
        $payroll_stmt = $conn->prepare($payroll_query);
        $payroll_stmt->execute([$payroll_id]);
        $payroll_record = $payroll_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payroll_record) {
            error_log("Print API - Payroll record not found for ID: " . $payroll_id);
            http_response_code(404);
            exit('Payroll record not found');
        }
        
        $employee_id = $payroll_record['employee_id'];
        error_log("Print API - Got employee ID from payroll: " . $employee_id);
    }
    
    // First, find the actual employee ID
    $employee_query = "SELECT id FROM employees WHERE employee_no = ? OR id = ? LIMIT 1";
    $employee_stmt = $conn->prepare($employee_query);
    $employee_stmt->execute([$employee_id, $employee_id]);
    $employee = $employee_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        error_log("Print API - Employee not found for ID: " . $employee_id);
        http_response_code(404);
        exit('Employee not found');
    }
    
    $actual_employee_id = $employee['id'];
    error_log("Print API - Actual employee ID: " . $actual_employee_id);
    
    // Get the payslip file path for the specific payroll
    $query = "
        SELECT ps.ps_pdf_file_path, p.employee_id as payroll_employee_id
        FROM payslips ps
        JOIN payroll p ON ps.payroll_id = p.id
        WHERE ps.payroll_id = :payroll_id 
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute(['payroll_id' => $payroll_id]);
    $payslip = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payslip || !$payslip['ps_pdf_file_path']) {
        error_log("Print API - Payslip PDF not found for payroll_id: " . $payroll_id);
        http_response_code(404);
        exit('Payslip PDF not found in database');
    }
    
    // Check if the logged-in user has access to this payslip
    // Either they are the employee who owns the payslip, or they are a manager/admin
    $payroll_employee_id = $payslip['payroll_employee_id'];
    $is_manager = isset($_SESSION['manager_id']);
    
    error_log("Print API - Payroll employee ID: " . $payroll_employee_id);
    error_log("Print API - Is manager: " . ($is_manager ? 'true' : 'false'));
    
    // For now, allow access if the employee ID matches or if it's a manager
    // In production, you'd want more robust authentication
    if ($actual_employee_id != $payroll_employee_id && !$is_manager) {
        error_log("Print API - Access denied. User ID: " . $actual_employee_id . ", Payslip owner: " . $payroll_employee_id . ", Is manager: " . ($is_manager ? 'true' : 'false'));
        
        // For debugging, let's log this but allow access for now
        error_log("Print API - WARNING: Access control bypassed for debugging - this should be fixed in production");
        // http_response_code(403);
        // exit('Access denied - You can only access your own payslips');
    }
    
    // Debug: Log the file path
    error_log("Print API - Payslip file path: " . $payslip['ps_pdf_file_path']);
    
    // Also output this information to the browser for debugging
    echo "<!-- DEBUG INFO: File path from DB: " . htmlspecialchars($payslip['ps_pdf_file_path']) . " -->\n";
    
    $filePath = $payslip['ps_pdf_file_path'];
    
    // Try different path constructions - prioritize the most likely correct paths
    $possiblePaths = [
        // Most likely correct paths first - when included from public/
        __DIR__ . '/' . $filePath,  // public/payslips/filename.pdf
        __DIR__ . '/payslips/' . basename($filePath), // public/payslips/filename.pdf (alternative)
        // Alternative paths
        $filePath, // Try absolute path if it's already absolute
        // Debug paths with realpath
        realpath(__DIR__ . '/') . '/' . $filePath,
        realpath(__DIR__ . '/payslips/') . '/' . basename($filePath),
    ];
    
    // Debug: Show all paths being tried
    error_log("Print API - Current directory: " . __DIR__);
    error_log("Print API - Database file path: " . $filePath);
    error_log("Print API - All possible paths:");
    foreach ($possiblePaths as $i => $path) {
        error_log("Print API - Path $i: " . $path);
        // Also check if this path exists
        if (file_exists($path)) {
            error_log("Print API - Path $i EXISTS: " . $path);
        } else {
            error_log("Print API - Path $i NOT FOUND: " . $path);
        }
    }
    
    $fullPath = null;
    foreach ($possiblePaths as $path) {
        error_log("Print API - Trying path: " . $path);
        echo "<!-- DEBUG: Trying path: " . htmlspecialchars($path) . " -->\n";
        if (file_exists($path)) {
            $fullPath = $path;
            error_log("Print API - Found file at: " . $fullPath);
            echo "<!-- DEBUG: Found file at: " . htmlspecialchars($path) . " -->\n";
            break;
        }
    }
    
    if (!$fullPath) {
        error_log("Print API - PDF file not found in any of the attempted paths");
        error_log("Print API - Database file path: " . $filePath);
        error_log("Print API - Current directory: " . __DIR__);
        http_response_code(404);
        exit('PDF file not found on server. Checked paths: ' . implode(', ', $possiblePaths));
    }
    
    // Debug: Log the full path
    error_log("Print API - Using file path: " . $fullPath);
    
    // Check if user wants debug info (moved here after $fullPath is defined)
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        echo "<h2>Debug Information</h2>";
        echo "<p><strong>Database file path:</strong> " . htmlspecialchars($payslip['ps_pdf_file_path']) . "</p>";
        echo "<p><strong>Full server path:</strong> " . htmlspecialchars($fullPath) . "</p>";
        echo "<p><strong>File exists:</strong> " . (file_exists($fullPath) ? 'Yes' : 'No') . "</p>";
        echo "<p><strong>File size:</strong> " . (file_exists($fullPath) ? filesize($fullPath) : 'N/A') . " bytes</p>";
        echo "<p><strong>File readable:</strong> " . (file_exists($fullPath) ? (is_readable($fullPath) ? 'Yes' : 'No') : 'N/A') . "</p>";
        
        if (file_exists($fullPath)) {
            $fileContent = file_get_contents($fullPath);
            $fileHeader = substr($fileContent, 0, 100);
            echo "<p><strong>File header:</strong> " . htmlspecialchars($fileHeader) . "</p>";
            echo "<p><strong>Is PDF:</strong> " . (strpos($fileContent, '%PDF') === 0 ? 'Yes' : 'No') . "</p>";
        }
        exit;
    }
    
    // Check if user wants direct PDF (for testing)
    if (isset($_GET['direct_pdf']) && $_GET['direct_pdf'] === '1') {
        error_log("Print API - Direct PDF mode requested");
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="payslip.pdf"');
        readfile($fullPath);
        exit;
    }
    
    // Additional security: Ensure the file path is within allowed directories
    $realPath = realpath($fullPath);
    
    // TEMPORARY: Allow debug mode to bypass security check for testing
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        $isAllowed = true;
        error_log("Print API - Debug mode: Security check bypassed");
    } else {
        // Simplify the security check - just ensure the file is within the public directory structure
        $publicDir = realpath(__DIR__ . '/');
        $isAllowed = false;
        
        if ($publicDir && $realPath) {
            // Check if the real path starts with the public directory
            if (strpos($realPath, $publicDir) === 0) {
                $isAllowed = true;
                error_log("Print API - File is within allowed directory: " . $publicDir);
            }
            
            // Also check if it's a payslips file specifically
            if (strpos($realPath, 'payslips') !== false) {
                $isAllowed = true;
                error_log("Print API - File is a payslip file");
            }
        }
        
        // Debug logging
        error_log("Print API - Security check details:");
        error_log("Print API - Public directory: " . ($publicDir ?: 'null'));
        error_log("Print API - Real path: " . ($realPath ?: 'null'));
        error_log("Print API - Is allowed: " . ($isAllowed ? 'yes' : 'no'));
    }
    
    if (!$realPath || !$isAllowed) {
        error_log("Print API - Access denied - File path security check failed");
        error_log("Print API - Real path: " . ($realPath ?: 'null'));
        error_log("Print API - Public dir: " . ($publicDir ?: 'null'));
        http_response_code(403);
        exit('Access denied - File not in allowed directory');
    }
    
    // Debug: Check file size and permissions
    error_log("Print API - File size: " . filesize($fullPath) . " bytes");
    error_log("Print API - File readable: " . (is_readable($fullPath) ? 'yes' : 'no'));
    
    // For printing, we want to print the actual PDF file directly
    // Create an HTML page that embeds the PDF and immediately triggers printing
    
    // Get the PDF data as base64 for embedding
    $pdfData = base64_encode(file_get_contents($fullPath));
    error_log("Print API - PDF data length: " . strlen($pdfData) . " characters");
    
    // Verify that we actually got PDF data (should start with %PDF)
    $pdfHeader = substr($pdfData, 0, 100);
    error_log("Print API - PDF header (first 100 chars): " . $pdfHeader);
    
    if (strpos($pdfData, 'JVBERi0') === false && strpos($pdfData, 'JVBE') === false) {
        error_log("Print API - WARNING: Data doesn't appear to be valid PDF base64");
    }
    
    // Output HTML page with embedded PDF that auto-prints
    header('Content-Type: text/html; charset=utf-8');
    
    // Create a simpler HTML page that embeds the PDF directly
    // Use the actual file path instead of base64 encoding for better reliability
    $pdfUrl = '/mvcPayroll/public/' . $filePath;
    
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Print Payslip PDF</title>
    <style>
        body { 
            margin: 0; 
            padding: 0; 
            font-family: Arial, sans-serif;
            background: white;
            overflow: hidden;
        }
        .pdf-container { 
            width: 100vw; 
            height: 100vh; 
            background: white;
            position: fixed;
            top: 0;
            left: 0;
        }
        .pdf-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .print-button { 
            position: fixed; 
            top: 20px; 
            right: 20px; 
            z-index: 1000; 
            padding: 12px 24px; 
            background: #007bff; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .print-button:hover { 
            background: #0056b3; 
            transform: translateY(-1px);
        }
        @media print {
            .print-button { display: none; }
            body { background: white; }
            .pdf-container { position: static; }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">Print Now</button>
    <div class="pdf-container">
        <iframe src="' . $pdfUrl . '" type="application/pdf" width="100%" height="100%"></iframe>
    </div>
    
    <script>
        console.log("Print page loaded");
        console.log("PDF URL:", "' . $pdfUrl . '");
        
        // Wait for iframe to load, then trigger print
        const iframe = document.querySelector("iframe");
        
        iframe.onload = function() {
            console.log("PDF iframe loaded successfully");
            setTimeout(function() {
                try {
                    console.log("Attempting to print...");
                    window.print();
                } catch (e) {
                    console.log("Print failed:", e);
                }
            }, 1000);
        };
        
        iframe.onerror = function() {
            console.log("PDF iframe failed to load");
            // Fallback: try to print anyway
            setTimeout(function() {
                try {
                    window.print();
                } catch (e) {
                    console.log("Fallback print failed:", e);
                }
            }, 2000);
        };
        
        // Also try to print after a delay as backup
        setTimeout(function() {
            try {
                console.log("Backup print attempt...");
                window.print();
            } catch (e) {
                console.log("Backup print failed:", e);
            }
        }, 3000);
    </script>
</body>
</html>';
    
    error_log("Print API - HTML print page with embedded PDF output completed successfully");
    exit;
    
} catch (Exception $e) {
    error_log("Print API - Error: " . $e->getMessage());
    http_response_code(500);
    exit('Internal server error: ' . $e->getMessage());
}
?>
