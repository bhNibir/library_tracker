<?php
// Common utility functions

// Function to display success message
function showSuccess($message) {
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">';
    echo '<p>' . $message . '</p>';
    echo '</div>';
}

// Function to display error message
function showError($message) {
    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">';
    echo '<p>' . $message . '</p>';
    echo '</div>';
}

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to redirect with message
function redirectWithMessage($location, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $location");
    exit();
}

// Function to display any stored messages
function displayMessages() {
    if (isset($_SESSION['message'])) {
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
        
        if ($type == 'success') {
            showSuccess($_SESSION['message']);
        } else {
            showError($_SESSION['message']);
        }
        
        // Clear the message after displaying
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// Function to check if a book is available
function isBookAvailable($conn, $bookId) {
    $stmt = $conn->prepare("SELECT available FROM books WHERE id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['available'] == 1;
    }
    
    return false;
}

// Function to update book availability
function updateBookAvailability($conn, $bookId, $available) {
    $stmt = $conn->prepare("UPDATE books SET available = ? WHERE id = ?");
    $stmt->bind_param("ii", $available, $bookId);
    return $stmt->execute();
}

// Function to format date
function formatDate($date) {
    return date("F j, Y", strtotime($date));
}
?> 