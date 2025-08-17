<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../app/core/SecureAuth.php";
require_once "../app/core/secure_session.php";

$auth = new SecureAuth();
require_once views_path("branch/login_manager");

// Check if form is submitted and login_type is manager
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login_type']) && $_POST['login_type'] === 'manager') {

    // Check if required fields are set
    if (isset($_POST['m_email']) && isset($_POST['m_password'])) {
        $email = strtolower(trim($_POST['m_email']));
        $password = $_POST['m_password'];

        try {
            $result = $auth->authenticateManager($email, $password);
            
            if ($result['success']) {
                // Success alert and redirect
                echo "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    </head>
                    <body>
                        <script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Login successful',
                                text: 'Redirecting...',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = '" . $result['redirect'] . "';
                            });
                        </script>
                    </body>
                    </html>
                ";
                exit;
            } else {
                // Authentication failed
                echo "
                    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Authentication Failed',
                            text: '" . addslashes($result['message']) . "'
                        });
                    </script>
                ";
            }
        } catch (Exception $e) {
            // Database error
            echo "
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Database Error',
                        text: 'Please try again later.'
                    });
                </script>
            ";
            error_log($e->getMessage());
        }
    } else {
        // Missing email or password
        echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Fields',
                    text: 'Please enter both email and password.'
                });
            </script>
        ";
    }
}
?>
