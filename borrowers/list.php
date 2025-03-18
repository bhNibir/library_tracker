<?php
session_start();
include '../db.php';
include '../utils.php';

$pageTitle = 'Borrower List';

// Handle search query
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$searchWhere = $search ? "WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'" : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Get total records for pagination
$totalQuery = "SELECT COUNT(*) as total FROM borrowers $searchWhere";
$totalResult = $conn->query($totalQuery);
$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get borrowers with active borrow count
$query = "SELECT b.*, (
            SELECT COUNT(*) FROM borrowed br 
            WHERE br.borrower_id = b.id AND br.return_date IS NULL
          ) as active_borrows
          FROM borrowers b
          $searchWhere
          ORDER BY b.created_at DESC 
          LIMIT $offset, $recordsPerPage";
$result = $conn->query($query);

include '../header.php';
?>

<!-- Borrowers List Content -->
<div class="container mx-auto">
    <!-- Display any flash messages -->
    <?php displayMessages(); ?>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Borrowers Management</h2>
        <div>
            <a href="add.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded inline-flex items-center">
                <i class="fas fa-plus mr-2"></i> Add New Borrower
            </a>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form action="" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" placeholder="Search by name, email or phone" value="<?php echo $search; ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded inline-flex items-center">
                <i class="fas fa-search mr-2"></i> Search
            </button>
            <?php if($search): ?>
                <a href="list.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded inline-flex items-center">
                    <i class="fas fa-times mr-2"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Borrowers Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if($result->num_rows > 0): ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Borrows</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900"><?php echo $row['name']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?php echo $row['email']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?php echo $row['phone'] ? $row['phone'] : '—'; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($row['active_borrows'] > 0): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800"><?php echo $row['active_borrows']; ?> books</span>
                                <?php else: ?>
                                    <span class="text-gray-500">None</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700 mr-3">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="text-red-500 hover:text-red-700" 
                                   onclick="return confirm('Are you sure you want to delete this borrower?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to 
                                <span class="font-medium"><?php echo min($offset + $recordsPerPage, $totalRecords); ?></span> of 
                                <span class="font-medium"><?php echo $totalRecords; ?></span> results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <?php if($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search='.$search : ''; ?>" 
                                       class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Previous</span>
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.$search : ''; ?>" 
                                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i == $page ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if($page < $totalPages): ?>
                                    <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search='.$search : ''; ?>" 
                                       class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Next</span>
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="p-6 text-center">
                <div class="py-6">
                    <i class="fas fa-users text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-xl">No borrowers found</p>
                    <?php if($search): ?>
                        <p class="text-gray-400 mt-2">Try a different search term or clear the search</p>
                        <a href="list.php" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                            View all borrowers
                        </a>
                    <?php else: ?>
                        <a href="add.php" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                            <i class="fas fa-plus mr-2"></i> Add your first borrower
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?> 