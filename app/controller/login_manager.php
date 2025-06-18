<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

 // Ensure session is started at the top

require_once "../app/core/database.php"; // Adjust path if needed
require_once views_path("branch/login_manager");

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['login_type'] === 'manager') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $db = new Database();
        $query = "SELECT * FROM managers WHERE email = :email LIMIT 1";
        $result = $db->query($query, ['email' => $email]);

        if ($result && count($result) > 0) {
            $manager = $result[0];

            if (password_verify($password, $manager['password'])) {
                // ✅ Login success
                $_SESSION['manager_id'] = $manager['id'];
                $_SESSION['manager_name'] = $manager['name'];

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
                // ❌ Wrong password
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
            // ❌ Email not found
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
}
?>
