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
        <div class="mx-auto w-24 h-24 mb-4 flex items-center justify-center rounded-full bg-blue-100">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-12 h-12 text-blue-600 animate-bounce">
                <path fill-rule="evenodd" d="M3.75 6A2.25 2.25 0 016 3.75h12A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6zm8.25 2.25a.75.75 0 00-1.5 0v3.75a.75.75 0 001.5 0V8.25zm-.75 7.5a1.125 1.125 0 100-2.25 1.125 1.125 0 000 2.25z" clip-rule="evenodd" />
            </svg>
        </div>

        <h1 class="text-6xl font-bold text-red-600">403</h1>
        <h2 class="text-2xl mt-4 text-gray-800 font-semibold">Manager Access Required</h2>
        <p class="text-gray-600 mt-2">
            You do not have permission to view this page.<br>Please log in as a manager.
        </p>

        <a href="index.php?payroll=login1&type=manager" class="mt-6 inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
            Go to Manager Login
        </a>
    </div>
</body>
</html>
