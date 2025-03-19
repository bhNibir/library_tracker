<?php
session_start();
include '../db.php';
include '../utils.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirectWithMessage("list.php", "Invalid borrow record ID.", "error");
}

$id = (int)$_GET['id'];

// Check if the record exists and get book_id
$checkQuery = "SELECT book_id, return_date FROM borrowed WHERE id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirectWithMessage("list.php", "Borrow record not found.", "error");
}

$borrowRecord = $result->fetch_assoc();

// Begin transaction
$conn->begin_transaction();

try {
    // If book is not returned yet, make it available again
    if ($borrowRecord['return_date'] === null) {
        $updateBookQuery = "UPDATE books SET available = 1 WHERE id = ?";
        $stmt = $conn->prepare($updateBookQuery);
        $stmt->bind_param("i", $borrowRecord['book_id']);
        $stmt->execute();
    }

    // Delete the borrow record
    $deleteQuery = "DELETE FROM borrowed WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    
    redirectWithMessage("list.php", "Borrow record deleted successfully!");
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    redirectWithMessage("list.php", "Error deleting borrow record: " . $e->getMessage(), "error");
}
?> 