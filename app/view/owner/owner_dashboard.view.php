<?php
$title = "Owner Dashboard";
require_once views_path("partials/header");
require_once views_path("owner/owner_sidebar");
?>
<style>
@media (max-width: 767px) {
    main#mainContent {
        padding-top: calc(var(--mobile-navbar-height, 3.5rem) + 0.5rem) !important;
    }
    
    header h1 {
        display: block !important;
        visibility: visible !important;
        padding-top: 0.5rem;
    }
}

@media (min-width: 768px) {
    main#mainContent {
        padding-top: 0 !important;
    }
}
</style>

<main id="mainContent" class="ml-0 md:ml-[256px] p-3 sm:p-4 md:p-6 bg-[#f8fbf8] min-h-screen">
    <header class="mb-4 md:mb-6 pt-2 sm:pt-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-0">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#133913] block" style="display: block !important; visibility: visible !important;">Welcome, <?= htmlspecialchars($_SESSION['owner_name'] ?? 'Owner') ?></h1>
                <p class="text-sm sm:text-base text-[#478547] mt-1">Overview of your company performance and quick links.</p>
            </div>
        </div>
    </header>

    <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border-2 border-green-200 rounded-lg p-4">
            <div class="text-sm text-[#478547]">Employees</div>
            <div class="text-3xl font-bold text-[#133913]">--</div>
            <div class="text-xs text-gray-500">Total active employees</div>
        </div>
        <div class="bg-white border-2 border-green-200 rounded-lg p-4">
            <div class="text-sm text-[#478547]">Managers</div>
            <div class="text-3xl font-bold text-[#133913]">--</div>
            <div class="text-xs text-gray-500">Total active managers</div>
        </div>
        <div class="bg-white border-2 border-green-200 rounded-lg p-4">
            <div class="text-sm text-[#478547]">Pending Leaves</div>
            <div class="text-3xl font-bold text-[#133913]">--</div>
            <div class="text-xs text-gray-500">Awaiting approval</div>
        </div>
    </section>

    <section class="mt-6">
        <div class="bg-white border-2 border-green-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xl font-semibold text-[#133913]">Quick Actions</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="index.php?payroll=owner_leavemanagement" class="btn btn-outline-success">Manage Leaves</a>
                <a href="index.php?payroll=owner_payroll" class="btn btn-success">Open Payroll</a>
            </div>
        </div>
    </section>
</main>

<?php require_once views_path("partials/footer"); ?>

<?php if (!empty($_SESSION['flash_owner_login_success'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    try {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Login Successfully!',
            text: 'Welcome back, <?= addslashes(htmlspecialchars($_SESSION['owner_name'] ?? 'Owner')) ?>',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    } catch (e) {}
});
</script>
<?php unset($_SESSION['flash_owner_login_success']); endif; ?>



