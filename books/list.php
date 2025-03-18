<?php
session_start();
include '../db.php';
include '../utils.php';

$pageTitle = 'Book List';

// Handle search query
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$searchWhere = $search ? "WHERE title LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%'" : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Get total records for pagination
$totalQuery = "SELECT COUNT(*) as total FROM books $searchWhere";
$totalResult = $conn->query($totalQuery);
$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get books
$query = "SELECT * FROM books $searchWhere ORDER BY created_at DESC LIMIT $offset, $recordsPerPage";
$result = $conn->query($query);

include '../header.php';
?>

<!-- Books List Content -->
<div class="container mx-auto">
    <!-- Display any flash messages -->
    <?php displayMessages(); ?>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Books Management</h2>
        <div>
            <a href="add.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded inline-flex items-center">
                <i class="fas fa-plus mr-2"></i> Add New Book
            </a>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form action="" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" placeholder="Search by title, author or ISBN" value="<?php echo $search; ?>"
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

    <!-- Books Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if($result->num_rows > 0): ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISBN</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900"><?php echo $row['title']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?php echo $row['author']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?php echo $row['isbn']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($row['available']): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Available</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Borrowed</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700 mr-3">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="text-red-500 hover:text-red-700" 
                                   onclick="return confirm('Are you sure you want to delete this book?');">
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
                    <i class="fas fa-book text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-xl">No books found</p>
                    <?php if($search): ?>
                        <p class="text-gray-400 mt-2">Try a different search term or clear the search</p>
                        <a href="list.php" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                            View all books
                        </a>
                    <?php else: ?>
                        <a href="add.php" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                            <i class="fas fa-plus mr-2"></i> Add your first book
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?> 