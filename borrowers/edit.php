<?php
session_start();
include '../db.php';
include '../utils.php';

$pageTitle = 'Edit Borrower';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirectWithMessage("list.php", "Invalid borrower ID.", "error");
}

$id = (int)$_GET['id'];

// Fetch borrower data
$query = "SELECT * FROM borrowers WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirectWithMessage("list.php", "Borrower not found.", "error");
}

$borrower = $result->fetch_assoc();

// Get active borrows
$activeBorrowsQuery = "SELECT COUNT(*) as count FROM borrowed WHERE borrower_id = ? AND return_date IS NULL";
$stmt = $conn->prepare($activeBorrowsQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$borrowsResult = $stmt->get_result();
$activeBorrows = $borrowsResult->fetch_assoc()['count'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } else {
        // Check if email already exists (excluding the current borrower)
        $checkQuery = "SELECT id FROM borrowers WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "A borrower with this email already exists.";
        }
    }
    
    // If no errors, update the borrower
    if (empty($errors)) {
        $query = "UPDATE borrowers SET name = ?, email = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $name, $email, $phone, $id);
        
        if ($stmt->execute()) {
            redirectWithMessage("list.php", "Borrower updated successfully!");
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
    }
}

include '../header.php';
?>

<!-- Edit Borrower Content -->
<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Edit Borrower</h2>
        <a href="list.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Borrowers
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

    <!-- Edit Borrower Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form action="" method="POST">
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : $borrower['name']; ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    required>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : $borrower['email']; ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    required>
            </div>

            <div class="mb-6">
                <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone Number (Optional)</label>
                <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : $borrower['phone']; ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <?php if ($activeBorrows > 0): ?>
            <div class="mb-6 p-4 bg-blue-50 rounded">
                <div class="flex items-center">
                    <div class="text-blue-500 mr-3">
                        <i class="fas fa-info-circle text-xl"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-blue-800">Active Borrow Information</p>
                        <p class="text-blue-600">This borrower currently has <?php echo $activeBorrows; ?> active 
                        <?php echo $activeBorrows === 1 ? 'borrow' : 'borrows'; ?>.</p>
                        <a href="../borrowed/list.php?search=<?php echo urlencode($borrower['name']); ?>" class="text-blue-700 hover:underline mt-1 inline-block">
                            <i class="fas fa-eye mr-1"></i> View borrowed books
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="flex items-center justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded focus:outline-none focus:shadow-outline">
                    <i class="fas fa-save mr-2"></i> Update Borrower
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?> 