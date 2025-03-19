<?php
session_start();
include '../db.php';
include '../utils.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirectWithMessage("list.php", "Invalid borrower ID.", "error");
}

$id = (int)$_GET['id'];

// Check if the borrower exists
$checkQuery = "SELECT id FROM borrowers WHERE id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirectWithMessage("list.php", "Borrower not found.", "error");
}

// Check if the borrower has active borrows
$activeBorrowsQuery = "SELECT COUNT(*) as count FROM borrowed WHERE borrower_id = ? AND return_date IS NULL";
$stmt = $conn->prepare($activeBorrowsQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$borrowsResult = $stmt->get_result();
$activeBorrows = $borrowsResult->fetch_assoc()['count'];

if ($activeBorrows > 0) {
    redirectWithMessage("list.php", "Cannot delete borrower with active borrows. Please return all books first.", "error");
}

// Delete the borrower
$deleteQuery = "DELETE FROM borrowers WHERE id = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    redirectWithMessage("list.php", "Borrower deleted successfully!");
} else {
    redirectWithMessage("list.php", "Error deleting borrower: " . $stmt->error, "error");
}
?> 