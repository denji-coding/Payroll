<?php
$title = "My Profile";
require_once views_path("partials/header");
require_once "../app/core/database.php";
echo '<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>';
echo '<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>';

$employeeNo = $_SESSION['employee_no'] ?? null;
if (!$employeeNo) {
    echo "Unauthorized access.";
    exit;
}

$db = new Database();
$result = $db->query("SELECT * FROM employees WHERE employee_no = ?", [$employeeNo]);
$user = $result[0] ?? null;
if (!$user) {
    echo "Employee not found.";
    exit;
}

$firstName = ucwords(strtolower($user['first_name'] ?? ''));
$middleName = strtoupper(substr($user['middle_name'] ?? '', 0, 1));
$lastName = ucwords(strtolower($user['last_name'] ?? ''));
$fullName = $firstName . ($middleName ? " {$middleName}." : "") . " $lastName";

$isMobile = '<script>document.write(window.innerWidth < 768 ? "true" : "false");</script>';
?>

<div class="flex min-h-screen overflow-hidden <?= $isMobile ? 'bg-gray-100' : '' ?>">    

    <main id="mainContent" class="flex-1 p-6 bg-gray-100 transition-all duration-300 ease-in-out ">

        <?php require_once views_path("partials/user_sidebar"); ?>
        <div class="mt-6">
            <span class="text-2xl font-bold tracking-tight">My Profile</span>
            <p class="text-gray-600">View and manage your personal information.</p>
        </div>

        <div class="max-w-4xl border-2 border-emerald-600 mx-auto mt-8 bg-white shadow-sm rounded-lg p-6">
            <div class="flex flex-col items-center text-center space-y-4 mt-2">
                <img src="<?= $user['photo_path'] ? htmlspecialchars($user['photo_path']) : '../public/assets/image/default-profile.png' ?>"
                     alt="Profile Photo"
                     class="w-32 h-32 rounded-full border-2 border-emerald-600 shadow">

                <p class="text-3xl text-gray-400"><?= htmlspecialchars($fullName) ?></p>

                <div class="flex flex-row gap-6">
                    <div>
                        <p class="text-sm text-gray-400"><?= htmlspecialchars($user['employee_no']) ?> | <?= htmlspecialchars($user['position'] ?? 'Employee') ?></p>
                    </div>
                </div>
                <div class="-mt-4 mb-4">
                    <a href="index.php?payroll=edit_profile" class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded shadow-sm">
                        <i class="bi bi-pencil-fill mr-2"></i> Edit Profile
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Email</label>
                    <p class="text-gray-800 bg-gray-100 p-2 rounded"><?= htmlspecialchars($user['email']) ?></p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Contact Number</label>
                    <p class="text-gray-800 bg-gray-100 p-2 rounded"><?= htmlspecialchars($user['contact_number']) ?></p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Position</label>
                    <p class="text-gray-800 bg-gray-100 p-2 rounded"><?= ucwords(htmlspecialchars($user['position'])) ?></p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Address</label>
                    <p class="text-gray-800 bg-gray-100 p-2 rounded"><?= ucwords(htmlspecialchars($user['address'])) ?></p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Date of Birth</label>
                    <p class="text-gray-800 bg-gray-100 p-2 rounded"><?= htmlspecialchars(date("F d, Y", strtotime($user['dob']))) ?></p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Place of Birth</label>
                    <p class="text-gray-800 bg-gray-100 p-2 rounded"><?= ucwords(htmlspecialchars($user['place_of_birth'])) ?></p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Sex</label>
                    <p class="text-gray-800 bg-gray-100 p-2 rounded"><?= ucwords(htmlspecialchars($user['sex'])) ?></p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Civil Status</label>
                    <p class="text-gray-800 bg-gray-100 p-2 rounded"><?= ucwords(htmlspecialchars($user['civil_status'])) ?></p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Citizenship</label>
                    <p class="text-gray-800 bg-gray-100 p-2 rounded"><?= ucwords(htmlspecialchars($user['citizenship'])) ?></p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Blood Type</label>
                    <p class="text-gray-800 bg-gray-100 p-2 rounded"><?= strtoupper(htmlspecialchars($user['blood_type'])) ?></p>
                </div>
            </div>
        </div>
    </main>
</div>
