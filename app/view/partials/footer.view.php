<!-- Load AOS (Animation on Scroll) -->
<script src="../public/assets/js/aos/aos.js"></script>

<!-- Custom JavaScript Files -->
<script src="../public/assets/js/dashboard.js"></script>
<script src="../public/assets/js/login.js"></script>
<script src="../public/assets/js/employees.js"></script>
<script src="../public/assets/js/edit_employee.js"></script>
<script src="../public/assets/js/delete_employee.js"></script>
<script src="../public/assets/js/view_employee.js"></script>
<script src="../public/assets/js/schedules.js"></script>

<!-- Font Awesome JS -->
<script src="../public/assets/js/fontawesome/all.min.js"></script>

<!-- SweetAlert2 -->
<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>

<!-- Bootstrap Bundle JS -->
<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>

<!-- Logout Confirmation with SweetAlert2 -->
<script>
    function confirmLogout(event) {
        event.preventDefault(); // Stop the default navigation

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to logout.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#396A39',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed with logout
                window.location.href = event.target.closest('a').href;
            }
        });
    }
</script>

</body>
</html>


<!-- Load AOS script -->
<!-- <script src="../public/assets/js/aos/aos.js"></script> -->

<!-- ✅ Conditional JS files -->
<!-- <?php if (isset($url) && $url === 'dashboard1'): ?>
    <script src="../public/assets/js/dashboard.js"></script>
<?php endif; ?>

<?php if (isset($url) && $url === 'login1'): ?>
    <script src="../public/assets/js/login.js"></script>
<?php endif; ?> -->

<!-- <?php if (isset($url) && in_array($url, ['dashboard1', 'employees'])): ?>
    <script src="../public/assets/js/employees.js"></script>
    <script src="../public/assets/js/edit_employee.js"></script>
    <script src="../public/assets/js/delete_employee.js"></script>
    <script src="../public/assets/js/view_employee.js"></script>
<?php endif; ?> -->

<!-- <?php if (isset($url) && $url === 'schedules'): ?>
    <script src="../public/assets/js/schedules.js"></script>
<?php endif; ?> -->

<!-- FontAwesome JS (optional unless needed) -->
<!-- <script src="../public/assets/js/fontawesome/all.min.js"></script> -->

<!-- SweetAlert2 -->
<!-- <script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script> -->

<!-- Bootstrap Bundle -->
<!-- <script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script> -->

<!-- ✅ Logout confirmation (can be always included or wrapped in a check) -->
<!-- <script>
    function confirmLogout(event) {
        event.preventDefault(); // stop the link from navigating

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to logout.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#396A39',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed with logout
                window.location.href = event.target.closest('a').href;
            }
        });
    }
</script>
</body>
</html> -->