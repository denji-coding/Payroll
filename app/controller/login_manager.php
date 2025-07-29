<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../app/core/database.php"; // Make sure this path is correct
require_once views_path("branch/login_manager");

// Check if form is submitted and login_type is manager
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login_type']) && $_POST['login_type'] === 'manager') {

    // Check if required fields are set
    if (isset($_POST['m_email']) && isset($_POST['m_password'])) {
        $email = strtolower(trim($_POST['m_email']));
        $password = $_POST['m_password'];

        try {
            $db = new Database();

            // Query for manager by email
            $query = "SELECT * FROM managers WHERE m_email = :email LIMIT 1";
            $result = $db->query($query, ['email' => $email]);

            if ($result && count($result) > 0) {
                $manager = $result[0];

                // Check if password is hashed or plain (for debugging)
                // If passwords are not hashed in DB, use: $password === $manager['m_password']
                if (password_verify($password, $manager['m_password'])) {
                    // Store session data
                    $_SESSION['manager_id'] = $manager['id'];
                    $_SESSION['manager_name'] = $manager['m_full_name'];

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
                                    window.location.href = 'index.php?payroll=manager_dashboard';
                                });
                            </script>
                        </body>
                        </html>
                    ";
                    exit;
                } else {
                    // Password incorrect
                    echo "
                        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                        <script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid Credentials',
                                text: 'Incorrect password.'
                            });
                        </script>
                    ";
                }
            } else {
                // Email not found
                echo "
                    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Account not found',
                            text: 'No manager registered with that email.'
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
