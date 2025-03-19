<?php
session_start();
include '../db.php';
include '../utils.php';

$pageTitle = 'Borrow a Book';

// Get available books for dropdown
$booksQuery = "SELECT id, title, author FROM books WHERE available = 1 ORDER BY title";
$booksResult = $conn->query($booksQuery);

// Get borrowers for dropdown
$borrowersQuery = "SELECT id, name, email FROM borrowers ORDER BY name";
$borrowersResult = $conn->query($borrowersQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = (int)$_POST['book_id'];
    $borrowerId = (int)$_POST['borrower_id'];
    $borrowDate = sanitizeInput($_POST['borrow_date']);
    $dueDate = sanitizeInput($_POST['due_date']);
    
    // Validate inputs
    $errors = [];
    
    if ($bookId <= 0) {
        $errors[] = "Please select a book.";
    } else {
        // Verify book exists and is available
        $bookCheckQuery = "SELECT id, available FROM books WHERE id = ?";
        $stmt = $conn->prepare($bookCheckQuery);
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $bookResult = $stmt->get_result();
        
        if ($bookResult->num_rows === 0) {
            $errors[] = "Selected book does not exist.";
        } else {
            $book = $bookResult->fetch_assoc();
            if ($book['available'] != 1) {
                $errors[] = "Selected book is not available for borrowing.";
            }
        }
    }
    
    if ($borrowerId <= 0) {
        $errors[] = "Please select a borrower.";
    } else {
        // Verify borrower exists
        $borrowerCheckQuery = "SELECT id FROM borrowers WHERE id = ?";
        $stmt = $conn->prepare($borrowerCheckQuery);
        $stmt->bind_param("i", $borrowerId);
        $stmt->execute();
        $borrowerResult = $stmt->get_result();
        
        if ($borrowerResult->num_rows === 0) {
            $errors[] = "Selected borrower does not exist.";
        }
    }
    
    if (empty($borrowDate)) {
        $errors[] = "Borrow date is required.";
    } elseif (strtotime($borrowDate) > time()) {
        $errors[] = "Borrow date cannot be in the future.";
    }
    
    if (empty($dueDate)) {
        $errors[] = "Due date is required.";
    } elseif (strtotime($dueDate) < strtotime($borrowDate)) {
        $errors[] = "Due date cannot be before the borrow date.";
    }
    
    // If no errors, insert the borrowed record and update book availability
    if (empty($errors)) {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Insert borrowed record
            $insertQuery = "INSERT INTO borrowed (book_id, borrower_id, borrow_date, due_date) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("iiss", $bookId, $borrowerId, $borrowDate, $dueDate);
            $stmt->execute();
            
            // Update book availability
            $updateQuery = "UPDATE books SET available = 0 WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("i", $bookId);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            redirectWithMessage("list.php", "Book borrowed successfully!");
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}

include '../header.php';
?>

<!-- Borrow Book Content -->
<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Borrow a Book</h2>
        <a href="list.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Borrowed Books
        </a>
    </div>

    <!-- Error Display -->
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Please fix the following errors:</p>
            <ul class="list-disc ml-5">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Warning if no books or borrowers -->
    <?php if($booksResult->num_rows === 0 || $borrowersResult->num_rows === 0): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
            <?php if($booksResult->num_rows === 0): ?>
                <p><strong>No available books found!</strong> Please <a href="../books/add.php" class="text-blue-600 hover:underline">add a book</a> first.</p>
            <?php endif; ?>
            
            <?php if($borrowersResult->num_rows === 0): ?>
                <p><strong>No borrowers found!</strong> Please <a href="../borrowers/add.php" class="text-blue-600 hover:underline">add a borrower</a> first.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Borrow Book Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form action="" method="POST">
            <div class="mb-4">
                <label for="book_id" class="block text-gray-700 text-sm font-bold mb-2">Book</label>
                <select id="book_id" name="book_id" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        required <?php echo $booksResult->num_rows === 0 ? 'disabled' : ''; ?>>
                    <option value="">-- Select a Book --</option>
                    <?php while($book = $booksResult->fetch_assoc()): ?>
                        <option value="<?php echo $book['id']; ?>" <?php echo isset($_POST['book_id']) && $_POST['book_id'] == $book['id'] ? 'selected' : ''; ?>>
                            <?php echo $book['title']; ?> (<?php echo $book['author']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="borrower_id" class="block text-gray-700 text-sm font-bold mb-2">Borrower</label>
                <select id="borrower_id" name="borrower_id" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        required <?php echo $borrowersResult->num_rows === 0 ? 'disabled' : ''; ?>>
                    <option value="">-- Select a Borrower --</option>
                    <?php while($borrower = $borrowersResult->fetch_assoc()): ?>
                        <option value="<?php echo $borrower['id']; ?>" <?php echo isset($_POST['borrower_id']) && $_POST['borrower_id'] == $borrower['id'] ? 'selected' : ''; ?>>
                            <?php echo $borrower['name']; ?> (<?php echo $borrower['email']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="borrow_date" class="block text-gray-700 text-sm font-bold mb-2">Borrow Date</label>
                <input type="date" id="borrow_date" name="borrow_date" value="<?php echo isset($_POST['borrow_date']) ? $_POST['borrow_date'] : date('Y-m-d'); ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    required>
            </div>

            <div class="mb-6">
                <label for="due_date" class="block text-gray-700 text-sm font-bold mb-2">Due Date</label>
                <input type="date" id="due_date" name="due_date" value="<?php echo isset($_POST['due_date']) ? $_POST['due_date'] : date('Y-m-d', strtotime('+14 days')); ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    required>
                <p class="text-sm text-gray-500 mt-1">Standard loan period is 14 days</p>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded focus:outline-none focus:shadow-outline"
                        <?php echo ($booksResult->num_rows === 0 || $borrowersResult->num_rows === 0) ? 'disabled' : ''; ?>>
                    <i class="fas fa-book mr-2"></i> Complete Borrow
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?> 