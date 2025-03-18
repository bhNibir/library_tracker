<?php
// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library_tracker";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Create tables if they don't exist
$books_table = "CREATE TABLE IF NOT EXISTS books (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(50) NOT NULL,
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$borrowers_table = "CREATE TABLE IF NOT EXISTS borrowers (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$borrowed_table = "CREATE TABLE IF NOT EXISTS borrowed (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    book_id INT(11) NOT NULL,
    borrower_id INT(11) NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (borrower_id) REFERENCES borrowers(id) ON DELETE CASCADE
)";

if ($conn->query($books_table) !== TRUE) {
    die("Error creating books table: " . $conn->error);
}

if ($conn->query($borrowers_table) !== TRUE) {
    die("Error creating borrowers table: " . $conn->error);
}

if ($conn->query($borrowed_table) !== TRUE) {
    die("Error creating borrowed table: " . $conn->error);
}
?> 