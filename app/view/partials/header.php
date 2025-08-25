<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'HRM & Payroll System' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="../public/assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="../public/icons/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../public/assets/css/fontawesome/all.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="../public/assets/js/sweetalert2/sweetalert2.all.min.css">
    
    <!-- AOS Animation -->
    <link href="../public/assets/css/aos/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../public/assets/css/dashboard.css">
    <link rel="stylesheet" href="../public/assets/css/login1.css">
    <link rel="stylesheet" href="../public/assets/css/attendance.css">
    
         <!-- Loading Spinner Styles -->
     <style>
         /* Page Loading Spinner */
         #pageLoadingSpinner {
             position: fixed;
             top: 0;
             left: 0;
             width: 100%;
             height: 100%;
             background: rgba(255, 255, 255, 0.98);
             backdrop-filter: blur(8px);
             display: flex;
             justify-content: center;
             align-items: center;
             z-index: 99999;
             opacity: 1;
             transition: opacity 0.3s ease-in-out;
         }
         
         .spinner-container {
             text-align: center;
             background: white;
             padding: 30px;
             border-radius: 15px;
             box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
             border: 1px solid rgba(22, 163, 74, 0.1);
         }
         
         .spinner {
             width: 60px;
             height: 60px;
             border: 5px solid #e5f2e5;
             border-top: 5px solid #16a34a;
             border-radius: 50%;
             animation: spin 1s linear infinite;
             margin: 0 auto 20px;
         }
         
         .spinner-text {
             color: #16a34a;
             font-size: 18px;
             font-weight: 600;
             margin-top: 15px;
             font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
         }
         
         @keyframes spin {
             0% { transform: rotate(0deg); }
             100% { transform: rotate(360deg); }
         }
         
         /* Hide spinner when page is loaded */
         .page-loaded #pageLoadingSpinner {
             opacity: 0;
             pointer-events: none;
         }
         
         /* Hide body scroll when loading */
         body:not(.page-loaded) {
             overflow: hidden;
         }
         
         /* Ensure spinner is always on top */
         #pageLoadingSpinner {
             pointer-events: none;
         }
         
         .spinner-container {
             pointer-events: auto;
         }
         
         /* Hide page content until loaded */
         body:not(.page-loaded) > *:not(#pageLoadingSpinner) {
             opacity: 0;
         }
         
         body.page-loaded > * {
             opacity: 1;
             transition: opacity 0.5s ease-in-out;
         }
     </style>
</head>
<body>
    <!-- Page Loading Spinner -->
    <div id="pageLoadingSpinner">
        <div class="spinner-container">
            <div class="spinner"></div>
            <div class="spinner-text">Loading...</div>
        </div>
    </div>

         <!-- Loading Spinner JavaScript -->
     <script>
         // Page Loading Spinner Functions
         function showPageLoader() {
             document.body.classList.remove('page-loaded');
             document.getElementById('pageLoadingSpinner').style.opacity = '1';
             document.getElementById('pageLoadingSpinner').style.display = 'flex';
         }
         
         function hidePageLoader() {
             document.body.classList.add('page-loaded');
             document.getElementById('pageLoadingSpinner').style.opacity = '0';
             setTimeout(() => {
                 document.getElementById('pageLoadingSpinner').style.display = 'none';
             }, 300);
         }
         
         // Show spinner immediately when page starts loading
         document.addEventListener('DOMContentLoaded', function() {
             // Ensure spinner is visible during page load
             showPageLoader();
             
             // Handle regular links with immediate feedback
             document.addEventListener('click', function(e) {
                 const link = e.target.closest('a');
                 if (link && !link.target && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
                     const href = link.getAttribute('href');
                     if (href && !href.startsWith('#') && !href.startsWith('javascript:') && !href.startsWith('mailto:') && !href.startsWith('tel:')) {
                         // Show loader immediately
                         showPageLoader();
                         
                         // Add a small delay to ensure spinner is visible
                         setTimeout(() => {
                             window.location.href = href;
                         }, 100);
                         
                         // Prevent default navigation
                         e.preventDefault();
                     }
                 }
             });
             
             // Handle form submissions
             document.addEventListener('submit', function(e) {
                 const form = e.target;
                 if (form && !form.hasAttribute('data-no-loader')) {
                     showPageLoader();
                     
                     // Add a small delay to ensure spinner is visible
                     setTimeout(() => {
                         form.submit();
                     }, 100);
                     
                     // Prevent immediate submission
                     e.preventDefault();
                 }
             });
         });
         
         // Show loader on page unload
         window.addEventListener('beforeunload', function() {
             showPageLoader();
         });
         
         // Show loader when page is about to unload
         window.addEventListener('pagehide', function() {
             showPageLoader();
         });
         
         // Hide loader when page is fully loaded
         window.addEventListener('load', function() {
             setTimeout(() => {
                 hidePageLoader();
             }, 1000); // Increased delay to ensure content is fully loaded
         });
         
         // Hide loader after a delay if load event doesn't fire
         setTimeout(function() {
             hidePageLoader();
         }, 3000);
         
         // Handle window.location changes
         const originalPushState = history.pushState;
         const originalReplaceState = history.replaceState;
         
         history.pushState = function() {
             showPageLoader();
             return originalPushState.apply(this, arguments);
         };
         
         history.replaceState = function() {
             showPageLoader();
             return originalReplaceState.apply(this, arguments);
         };
         
         // Show spinner immediately when script loads
         showPageLoader();
     </script>
