<?php
session_start();
include '../db.php';
include '../utils.php';

$pageTitle = 'Return a Book';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirectWithMessage("list.php", "Invalid borrow record ID.", "error");
}

$id = (int)$_GET['id'];

// Fetch borrow data
$query = "SELECT bo.*, b.title as book_title, br.name as borrower_name 
          FROM borrowed bo 
          JOIN books b ON bo.book_id = b.id 
          JOIN borrowers br ON bo.borrower_id = br.id 
          WHERE bo.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirectWithMessage("list.php", "Borrow record not found.", "error");
}

$borrow = $result->fetch_assoc();

// Check if already returned
if ($borrow['return_date'] !== null) {
    redirectWithMessage("list.php", "This book has already been returned.", "error");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $returnDate = sanitizeInput($_POST['return_date']);
    
    // Validate return date
    $errors = [];
    
    if (empty($returnDate)) {
        $errors[] = "Return date is required.";
    } elseif (strtotime($returnDate) < strtotime($borrow['borrow_date'])) {
        $errors[] = "Return date cannot be before the borrow date.";
    } elseif (strtotime($returnDate) > time()) {
        $errors[] = "Return date cannot be in the future.";
    }
    
    // If no errors, update the record and book availability
    if (empty($errors)) {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update borrowed record
            $updateBorrowQuery = "UPDATE borrowed SET return_date = ? WHERE id = ?";
            $stmt = $conn->prepare($updateBorrowQuery);
            $stmt->bind_param("si", $returnDate, $id);
            $stmt->execute();
            
            // Update book availability
            $updateBookQuery = "UPDATE books SET available = 1 WHERE id = ?";
            $stmt = $conn->prepare($updateBookQuery);
            $stmt->bind_param("i", $borrow['book_id']);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            redirectWithMessage("list.php", "Book returned successfully!");
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}

include '../header.php';
?>

<!-- Return Book Content -->
<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Return a Book</h2>
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

    <!-- Book Information -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Book Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-gray-600">Book Title:</p>
                <p class="font-medium"><?php echo $borrow['book_title']; ?></p>
            </div>
            <div>
                <p class="text-gray-600">Borrower:</p>
                <p class="font-medium"><?php echo $borrow['borrower_name']; ?></p>
            </div>
            <div>
                <p class="text-gray-600">Borrow Date:</p>
                <p class="font-medium"><?php echo formatDate($borrow['borrow_date']); ?></p>
            </div>
            <div>
                <p class="text-gray-600">Due Date:</p>
                <p class="font-medium">
                    <?php echo formatDate($borrow['due_date']); ?>
                    <?php if (strtotime($borrow['due_date']) < time()): ?>
                        <span class="text-red-500 ml-2">Overdue</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <?php 
        // Calculate days overdue or days remaining
        $currentDate = time();
        $dueDate = strtotime($borrow['due_date']);
        $daysDiff = floor(($dueDate - $currentDate) / 86400);
        
        if ($daysDiff < 0): 
        ?>
            <div class="mt-4 bg-red-50 text-red-700 p-3 rounded">
                <p class="font-medium">This book is overdue by <?php echo abs($daysDiff); ?> days</p>
            </div>
        <?php else: ?>
            <div class="mt-4 bg-blue-50 text-blue-700 p-3 rounded">
                <p class="font-medium">This book is due in <?php echo $daysDiff; ?> days</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Return Book Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form action="" method="POST">
            <div class="mb-6">
                <label for="return_date" class="block text-gray-700 text-sm font-bold mb-2">Return Date</label>
                <input type="date" id="return_date" name="return_date" value="<?php echo isset($_POST['return_date']) ? $_POST['return_date'] : date('Y-m-d'); ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    required>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded focus:outline-none focus:shadow-outline">
                    <i class="fas fa-undo mr-2"></i> Confirm Return
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?> 