<?php
session_start();
include '../db.php';
include '../utils.php';

$pageTitle = 'Borrowed Books';

// Handle search query
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$filter = isset($_GET['filter']) ? sanitizeInput($_GET['filter']) : '';

// Build the WHERE clause
$whereConditions = [];
$params = [];
$types = '';

if ($search) {
    $whereConditions[] = "(b.title LIKE ? OR br.name LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ss';
}

if ($filter === 'overdue') {
    $whereConditions[] = "bo.due_date < CURDATE() AND bo.return_date IS NULL";
} elseif ($filter === 'returned') {
    $whereConditions[] = "bo.return_date IS NOT NULL";
} elseif ($filter === 'active') {
    $whereConditions[] = "bo.return_date IS NULL";
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Get total records for pagination
$totalQuery = "SELECT COUNT(*) as total FROM borrowed bo 
               JOIN books b ON bo.book_id = b.id 
               JOIN borrowers br ON bo.borrower_id = br.id 
               $whereClause";

if (!empty($params)) {
    $stmt = $conn->prepare($totalQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $totalResult = $stmt->get_result();
} else {
    $totalResult = $conn->query($totalQuery);
}

$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get borrowed books
$query = "SELECT bo.*, b.title, b.author, br.name as borrower_name
          FROM borrowed bo 
          JOIN books b ON bo.book_id = b.id 
          JOIN borrowers br ON bo.borrower_id = br.id 
          $whereClause
          ORDER BY bo.borrow_date DESC 
          LIMIT ?, ?";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $params[] = $offset;
    $params[] = $recordsPerPage;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $offset, $recordsPerPage);
}
$stmt->execute();
$result = $stmt->get_result();

include '../header.php';
?>

<!-- Borrowed Books List Content -->
<div class="container mx-auto">
    <!-- Display any flash messages -->
    <?php displayMessages(); ?>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Borrowed Books Management</h2>
        <div>
            <a href="borrow.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded inline-flex items-center">
                <i class="fas fa-plus mr-2"></i> New Borrow
            </a>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form action="" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" placeholder="Search by book title or borrower name" value="<?php echo $search; ?>"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <select name="filter" class="px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" <?php echo $filter === '' ? 'selected' : ''; ?>>All Borrows</option>
                    <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Active Borrows</option>
                    <option value="overdue" <?php echo $filter === 'overdue' ? 'selected' : ''; ?>>Overdue Books</option>
                    <option value="returned" <?php echo $filter === 'returned' ? 'selected' : ''; ?>>Returned Books</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded inline-flex items-center">
                <i class="fas fa-search mr-2"></i> Search
            </button>
            <?php if($search || $filter): ?>
                <a href="list.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded inline-flex items-center">
                    <i class="fas fa-times mr-2"></i> Reset Filters
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Borrowed Books Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php if($result->num_rows > 0): ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrow Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo $row['title']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $row['author']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $row['borrower_name']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo formatDate($row['borrow_date']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo formatDate($row['due_date']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($row['return_date']): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                        Returned on <?php echo formatDate($row['return_date']); ?>
                                    </span>
                                <?php elseif (strtotime($row['due_date']) < time()): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                        Overdue by <?php echo floor((time() - strtotime($row['due_date'])) / 86400); ?> days
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        Due in <?php echo floor((strtotime($row['due_date']) - time()) / 86400); ?> days
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if (!$row['return_date']): ?>
                                    <a href="return.php?id=<?php echo $row['id']; ?>" class="text-green-500 hover:text-green-700 mr-3">
                                        <i class="fas fa-undo"></i> Return
                                    </a>
                                <?php endif; ?>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="text-red-500 hover:text-red-700" 
                                   onclick="return confirm('Are you sure you want to delete this record?');">
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
                                    <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search='.$search : ''; ?><?php echo $filter ? '&filter='.$filter : ''; ?>" 
                                       class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Previous</span>
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.$search : ''; ?><?php echo $filter ? '&filter='.$filter : ''; ?>" 
                                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i == $page ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if($page < $totalPages): ?>
                                    <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search='.$search : ''; ?><?php echo $filter ? '&filter='.$filter : ''; ?>" 
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
                    <i class="fas fa-exchange-alt text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-xl">No borrowed books found</p>
                    <?php if($search || $filter): ?>
                        <p class="text-gray-400 mt-2">Try different search terms or filters</p>
                        <a href="list.php" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                            View all borrowed books
                        </a>
                    <?php else: ?>
                        <a href="borrow.php" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                            <i class="fas fa-plus mr-2"></i> Register a new borrow
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?> 