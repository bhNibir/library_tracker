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
                        primary: '#4f46e5', // Indigo color
                        secondary: '#6b7280',
                        accent: '#10b981', // Emerald color
                    }
                }
            }
        }
    </script>
    <style>
        .active {
            background-color: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
            border-left: 3px solid #4f46e5;
        }
        .nav-link {
            color: #4b5563;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .nav-link:hover {
            color: #4f46e5;
            background-color: rgba(79, 70, 229, 0.1);
            border-left: 3px solid #4f46e5;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg flex flex-col">
            <div class="p-6 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white">
                <h1 class="text-2xl font-bold">Library Tracker</h1>
                <p class="text-sm text-indigo-100 mt-1">Book Management System</p>
            </div>
            <nav class="mt-2 flex-1 overflow-y-auto">
                <ul>
                    <li class="px-3 py-2">
                        <a href="/library_tracker/index.php" class="nav-link flex items-center space-x-2 rounded-lg px-3 py-3" data-page="index.php">
                            <i class="fas fa-home w-6 text-center"></i>
                            <span class="font-medium">Dashboard</span>
                        </a>
                    </li>
                    <li class="px-3 py-2">
                        <a href="/library_tracker/books/list.php" class="nav-link flex items-center space-x-2 rounded-lg px-3 py-3" data-page="books">
                            <i class="fas fa-book w-6 text-center"></i>
                            <span class="font-medium">Books</span>
                        </a>
                    </li>
                    <li class="px-3 py-2">
                        <a href="/library_tracker/borrowers/list.php" class="nav-link flex items-center space-x-2 rounded-lg px-3 py-3" data-page="borrowers">
                            <i class="fas fa-users w-6 text-center"></i>
                            <span class="font-medium">Borrowers</span>
                        </a>
                    </li>
                    <li class="px-3 py-2">
                        <a href="/library_tracker/borrowed/list.php" class="nav-link flex items-center space-x-2 rounded-lg px-3 py-3" data-page="borrowed">
                            <i class="fas fa-exchange-alt w-6 text-center"></i>
                            <span class="font-medium">Borrowed Books</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- Bottom section with info -->
            <div class="bg-gray-50 p-4 border-t border-gray-200">
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-2 text-accent"></i>
                    <span>University DBMS Project</span>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="flex items-center justify-between bg-white p-4 shadow-sm">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800"><?php echo isset($pageTitle) ? $pageTitle : 'Library Book Tracker'; ?></h2>
                </div>
                <div class="flex items-center">
                    <a href="/library_tracker/borrowed/borrow.php" class="bg-accent hover:bg-green-600 text-white px-3 py-2 rounded-md text-sm font-medium mr-3 transition-colors">
                        <i class="fas fa-plus mr-1"></i> Borrow Book
                    </a>
                </div>
            </header>
            
            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
             
           