<?php
session_start();
include 'db.php';
include 'utils.php';

$pageTitle = 'Dashboard';

// Get total counts
$booksQuery = "SELECT COUNT(*) as total_books FROM books";
$borrowersQuery = "SELECT COUNT(*) as total_borrowers FROM borrowers";
$borrowedQuery = "SELECT COUNT(*) as total_borrowed FROM borrowed WHERE return_date IS NULL";
$overDueQuery = "SELECT COUNT(*) as total_overdue FROM borrowed WHERE due_date < CURDATE() AND return_date IS NULL";

$booksResult = $conn->query($booksQuery);
$borrowersResult = $conn->query($borrowersQuery);
$borrowedResult = $conn->query($borrowedQuery);
$overDueResult = $conn->query($overDueQuery);

$booksCount = $booksResult->fetch_assoc()['total_books'];
$borrowersCount = $borrowersResult->fetch_assoc()['total_borrowers'];
$borrowedCount = $borrowedResult->fetch_assoc()['total_borrowed'];
$overdueCount = $overDueResult->fetch_assoc()['total_overdue'];

// Get recent borrowed books
$recentBorrowedQuery = "SELECT b.title, br.name, bo.borrow_date, bo.due_date 
                        FROM borrowed bo 
                        JOIN books b ON bo.book_id = b.id 
                        JOIN borrowers br ON bo.borrower_id = br.id 
                        WHERE bo.return_date IS NULL 
                        ORDER BY bo.borrow_date DESC 
                        LIMIT 5";
$recentBorrowedResult = $conn->query($recentBorrowedQuery);

include 'header.php';
?>

<!-- Dashboard Content -->
<div class="container mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Books Card -->
        <div class="bg-white rounded-lg shadow p-6 h-full flex flex-col">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-primary">
                    <i class="fas fa-book text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Total Books</p>
                    <h3 class="text-2xl font-bold"><?php echo $booksCount; ?></h3>
                </div>
            </div>
            <a href="books/list.php" class="mt-auto pt-4 inline-block text-sm text-primary hover:text-indigo-700">View All Books <i class="fas fa-arrow-right ml-1"></i></a>
        </div>

        <!-- Borrowers Card -->
        <div class="bg-white rounded-lg shadow p-6 h-full flex flex-col">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-primary">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Total Borrowers</p>
                    <h3 class="text-2xl font-bold"><?php echo $borrowersCount; ?></h3>
                </div>
            </div>
            <a href="borrowers/list.php" class="mt-auto pt-4 inline-block text-sm text-primary hover:text-indigo-700">View All Borrowers <i class="fas fa-arrow-right ml-1"></i></a>
        </div>

        <!-- Borrowed Books Card -->
        <div class="bg-white rounded-lg shadow p-6 h-full flex flex-col">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-primary">
                    <i class="fas fa-exchange-alt text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Currently Borrowed</p>
                    <h3 class="text-2xl font-bold"><?php echo $borrowedCount; ?></h3>
                </div>
            </div>
            <a href="borrowed/list.php" class="mt-auto pt-4 inline-block text-sm text-primary hover:text-indigo-700">View All Borrowed <i class="fas fa-arrow-right ml-1"></i></a>
        </div>

        <!-- Overdue Books Card -->
        <div class="bg-white rounded-lg shadow p-6 h-full flex flex-col">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-500">
                    <i class="fas fa-exclamation-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Overdue Books</p>
                    <h3 class="text-2xl font-bold"><?php echo $overdueCount; ?></h3>
                </div>
            </div>
            <a href="borrowed/list.php?filter=overdue" class="mt-auto pt-4 inline-block text-sm text-red-500 hover:text-red-700">View Overdue Books <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
    </div>

    <!-- Recent Borrowed Books -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200 px-6 py-4 bg-gradient-to-r from-indigo-600 to-indigo-700">
            <h3 class="text-lg font-medium text-white">Recent Borrowed Books</h3>
        </div>
        <div class="p-6">
            <?php if ($recentBorrowedResult->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrow Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = $recentBorrowedResult->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['title']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['name']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo formatDate($row['borrow_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo formatDate($row['due_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (strtotime($row['due_date']) < time()): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Overdue</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-primary">Active</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">No books currently borrowed.</p>
            <?php endif; ?>
            <div class="mt-4">
                <a href="borrowed/borrow.php" class="px-4 py-2 bg-accent hover:bg-green-600 text-white rounded transition-colors">
                    <i class="fas fa-plus mr-2"></i> Borrow a Book
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 