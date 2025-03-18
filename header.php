<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Book Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#6b7280',
                    }
                }
            }
        }
    </script>
    <style>
        .active {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-md">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-primary">Library Tracker</h1>
                <p class="text-sm text-gray-500">Book Management System</p>
            </div>
            <nav class="mt-6">
                <ul>
                    <li class="px-6 py-3">
                        <a href="/library_tracker/index.php" class="flex items-center space-x-2 text-gray-700 hover:text-primary hover:bg-blue-50 rounded-lg px-3 py-2 transition-colors">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="px-6 py-3">
                        <a href="/library_tracker/books/list.php" class="flex items-center space-x-2 text-gray-700 hover:text-primary hover:bg-blue-50 rounded-lg px-3 py-2 transition-colors">
                            <i class="fas fa-book"></i>
                            <span>Books</span>
                        </a>
                    </li>
                    <li class="px-6 py-3">
                        <a href="/library_tracker/borrowers/list.php" class="flex items-center space-x-2 text-gray-700 hover:text-primary hover:bg-blue-50 rounded-lg px-3 py-2 transition-colors">
                            <i class="fas fa-users"></i>
                            <span>Borrowers</span>
                        </a>
                    </li>
                    <li class="px-6 py-3">
                        <a href="/library_tracker/borrowed/list.php" class="flex items-center space-x-2 text-gray-700 hover:text-primary hover:bg-blue-50 rounded-lg px-3 py-2 transition-colors">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Borrowed Books</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="flex items-center justify-between bg-white p-4 shadow-sm">
                <div>
                    <h2 class="text-xl font-semibold"><?php echo isset($pageTitle) ? $pageTitle : 'Library Book Tracker'; ?></h2>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-gray-600">University DBMS Project</span>
                </div>
            </header>
            
            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Page content goes here -->
            </main>
        </div>
    </div>
</body>
</html> 