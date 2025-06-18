<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once "../app/core/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $db = new Database();

        // Check for duplicate email
        $existing = $db->query("SELECT id FROM managers WHERE email = :email", ['email' => $email]);
        if ($existing && count($existing) > 0) {
            echo "
            <script src='../public/assets/js/sweetalert2/sweetalert2.all.min.js'></script>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Email already used',
                    text: 'Please use a different email.'
                }).then(() => window.history.back());
            </script>";
            exit;
        }

        // Hash and insert
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $db->query("INSERT INTO managers (name, email, password) VALUES (:name, :email, :password)", [
            'name' => $name,
            'email' => $email,
            'password' => $hashed
        ]);

        echo "
        <script src='../public/assets/js/sweetalert2/sweetalert2.all.min.js'></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Manager Registered',
                text: 'Redirecting to login...',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = 'login.php';
            });
        </script>";
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "
        <script src='../public/assets/js/sweetalert2/sweetalert2.all.min.js'></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error occurred',
                text: 'Please try again later.'
            });
        </script>";
    }
}

require views_path("branch/register_manager");