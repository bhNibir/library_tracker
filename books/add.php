<?php
session_start();
include '../db.php';
include '../utils.php';

$pageTitle = 'Add New Book';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $author = sanitizeInput($_POST['author']);
    $isbn = sanitizeInput($_POST['isbn']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "Title is required.";
    }
    
    if (empty($author)) {
        $errors[] = "Author is required.";
    }
    
    if (empty($isbn)) {
        $errors[] = "ISBN is required.";
    } else {
        // Check if ISBN already exists
        $checkQuery = "SELECT id FROM books WHERE isbn = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "A book with this ISBN already exists.";
        }
    }
    
    // If no errors, insert the book
    if (empty($errors)) {
        $query = "INSERT INTO books (title, author, isbn) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $title, $author, $isbn);
        
        if ($stmt->execute()) {
            redirectWithMessage("list.php", "Book added successfully!");
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
    }
}

include '../header.php';
?>

<!-- Add Book Content -->
<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Add New Book</h2>
        <a href="list.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Books
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

    <!-- Add Book Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form action="" method="POST">
            <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Book Title</label>
                <input type="text" id="title" name="title" value="<?php echo isset($_POST['title']) ? $_POST['title'] : ''; ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    required>
            </div>

            <div class="mb-4">
                <label for="author" class="block text-gray-700 text-sm font-bold mb-2">Author</label>
                <input type="text" id="author" name="author" value="<?php echo isset($_POST['author']) ? $_POST['author'] : ''; ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    required>
            </div>

            <div class="mb-6">
                <label for="isbn" class="block text-gray-700 text-sm font-bold mb-2">ISBN</label>
                <input type="text" id="isbn" name="isbn" value="<?php echo isset($_POST['isbn']) ? $_POST['isbn'] : ''; ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    required>
                <p class="text-sm text-gray-500 mt-1">Enter a unique ISBN identifier for the book</p>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded focus:outline-none focus:shadow-outline">
                    <i class="fas fa-save mr-2"></i> Save Book
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?> 