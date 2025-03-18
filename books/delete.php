<?php
session_start();
include '../db.php';
include '../utils.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirectWithMessage("list.php", "Invalid book ID.", "error");
}

$id = (int)$_GET['id'];

// Check if the book exists
$checkQuery = "SELECT * FROM books WHERE id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirectWithMessage("list.php", "Book not found.", "error");
}

$book = $result->fetch_assoc();

// Check if the book is currently borrowed
if (!$book['available']) {
    redirectWithMessage("list.php", "Cannot delete a book that is currently borrowed.", "error");
}

// Delete the book
$deleteQuery = "DELETE FROM books WHERE id = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    redirectWithMessage("list.php", "Book deleted successfully!");
} else {
    redirectWithMessage("list.php", "Error deleting book: " . $stmt->error, "error");
}
?> 