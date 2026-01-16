
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Access Denied</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="text-center max-w-md bg-white p-8 rounded-xl shadow-lg">

    <!-- Icon Animation (No external fetch) -->
    <div class="mx-auto w-24 h-24 mb-4 flex items-center justify-center rounded-full bg-red-100">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-12 h-12 text-red-600 animate-bounce">
        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v2.25H6A2.25 2.25 0 003.75 11.25v8.25A2.25 2.25 0 006 21.75h12a2.25 2.25 0 002.25-2.25v-8.25A2.25 2.25 0 0018 9H17.25V6.75A5.25 5.25 0 0012 1.5zm-3.75 5.25a3.75 3.75 0 117.5 0V9H8.25V6.75z" clip-rule="evenodd" />
      </svg>
    </div>

    <h1 class="text-6xl font-bold text-red-600">403</h1>
    <h2 class="text-2xl mt-4 text-gray-800 font-semibold">Access Denied</h2>
    <p class="text-gray-600 mt-2">
      You do not have permission to view this page.
    </p>

    <a href="index.php?payroll=login_admin" class="mt-6 inline-block px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
      Return to Login
    </a>
  </div>

</body>
</html>