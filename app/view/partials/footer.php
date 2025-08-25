    <!-- Bootstrap JS -->
    <script src="../public/assets/js/bootstrap/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="../public/assets/js/sweetalert2/sweetalert2.all.min.js"></script>
    
    <!-- AOS Animation -->
    <script src="../public/assets/js/aos/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    </script>
    
    <!-- Additional Loading Spinner Functions -->
    <script>
        // Manual control functions for loading spinner
        window.PageLoader = {
            // Show the loading spinner
            show: function(message = 'Loading...') {
                const spinnerText = document.querySelector('.spinner-text');
                if (spinnerText) {
                    spinnerText.textContent = message;
                }
                showPageLoader();
            },
            
            // Hide the loading spinner
            hide: function() {
                hidePageLoader();
            },
            
            // Show spinner with custom message for a specific duration
            showFor: function(duration = 2000, message = 'Loading...') {
                this.show(message);
                setTimeout(() => {
                    this.hide();
                }, duration);
            },
            
            // Show spinner for AJAX requests
            showForAjax: function(message = 'Loading...') {
                this.show(message);
            }
        };
        
        // Override fetch to automatically show/hide spinner
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            PageLoader.showForAjax('Loading data...');
            
            return originalFetch.apply(this, args)
                .then(response => {
                    PageLoader.hide();
                    return response;
                })
                .catch(error => {
                    PageLoader.hide();
                    throw error;
                });
        };
        
        // Example usage for manual navigation
        function navigateWithLoader(url, message = 'Navigating...') {
            PageLoader.show(message);
            setTimeout(() => {
                window.location.href = url;
            }, 100);
        }
        
        // Example usage for form submission with custom loader
        function submitFormWithLoader(formId, message = 'Submitting...') {
            const form = document.getElementById(formId);
            if (form) {
                PageLoader.show(message);
                form.submit();
            }
        }
    </script>
</body>
</html>
