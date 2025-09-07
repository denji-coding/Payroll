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
        <path fill-rule="evenodd" d="M10.5 3.75a3.75 3.75 0 00-3.75 3.75V9H6A2.25 2.25 0 003.75 11.25v7.5A2.25 2.25 0 006 21h12a2.25 2.25 0 002.25-2.25v-7.5A2.25 2.25 0 0018 9h-.75V7.5a3.75 3.75 0 00-7.5 0V9H10.5V7.5a2.25 2.25 0 114.5 0V9H10.5z" clip-rule="evenodd" />
      </svg>
    </div>

    <h1 class="text-6xl font-bold text-red-600">403</h1>
    <h2 class="text-2xl mt-4 text-gray-800 font-semibold">Access Denied</h2>
    <p class="text-gray-600 mt-2">
      You do not have permission to view this page.<br>Please log in as a employee.
    </p>

    <a href="index.php?payroll=login1&type=employee" class="mt-6 inline-block px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
      Return to Login
    </a>
  </div>

</body>
</html>