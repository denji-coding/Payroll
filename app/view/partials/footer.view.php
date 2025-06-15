
<!-- Load AOS script -->
<script src="../public/assets/js/aos/aos.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script> -->


<!-- Javascript File-->
<script src="../public/assets/js/dashboard.js"></script>
<script src="../public/assets/js/login.js"></script>
<script src="../public/assets/js/employees.js"></script>
<script src="../public/assets/js/edit_employee.js"></script>
<script src="../public/assets/js/delete_employee.js"></script>
<script src="../public/assets/js/view_employee.js"></script>
<script src="../public/assets/js/schedules.js"></script>

<script src="../public\assets\js\fontawesome\all.min.js"></script>


<!-- Tailwind CSS -->
<!-- <script src="https://cdn.tailwindcss.com"></script> -->

<!-- SweetAlert2 -->
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
<script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>



<!-- Animation on scroll library -->
<!-- <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script> -->
<script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>


<!-- Script for Log Out Confirmation Toast -->


<script>

</script>
</body>
<script>
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


</html>