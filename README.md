# Library Book Tracker

A complete Library Book Tracker system built with PHP and MySQL for DBMS university course. This CRUD-based application helps manage books, borrowers, and borrow transactions.

## Features

### Book Management
- Add new books (title, author, ISBN)
- View available books
- Update book details
- Delete books

### Borrower Management
- Add new borrowers (name, email, contact)
- View borrower list
- Update borrower info
- Delete borrowers

### Book Borrowing System
- Record book borrow transactions (borrower, book, due date)
- Track due and returned books
- View overdue books
- Delete completed borrow records

## Technical Details

- **Frontend:** HTML, CSS (Tailwind CSS), JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Icons:** Font Awesome

## Project Structure

```
library_tracker/
├── db.php            # Database connection and setup
├── utils.php         # Utility functions
├── header.php        # Common header
├── footer.php        # Common footer
├── index.php         # Dashboard
├── books/            # Book management
│    ├── add.php
│    ├── list.php
│    ├── edit.php
│    └── delete.php
├── borrowers/        # Borrower management
│    ├── add.php
│    ├── list.php
│    ├── edit.php
│    └── delete.php
└── borrowed/         # Borrow management
     ├── borrow.php
     ├── return.php
     ├── list.php
     └── delete.php
```

## Setup Instructions

1. **Database Setup:**
   - The application will automatically create the database and tables when you first run it.
   - Ensure your MySQL server is running and you have appropriate credentials.
   - Default configuration uses:
     - Host: localhost
     - Username: root
     - Password: (empty)
     - Database name: library_tracker

2. **Deployment:**
   - Place the entire `library_tracker` folder in your web server's document root (e.g., htdocs for XAMPP).
   - Access the application via your web browser at `http://localhost/library_tracker/`.

3. **Required Software:**
   - PHP 7.0 or higher
   - MySQL 5.6 or higher
   - Web server (Apache, Nginx, etc.)

## Usage

1. **Books Management:**
   - Add books with unique ISBN
   - Edit book details
   - View all books with availability status
   - Delete books (only if they are not currently borrowed)

2. **Borrowers Management:**
   - Add borrowers with unique email
   - Edit borrower details
   - View all borrowers and their current borrow count
   - Delete borrowers (only if they have no active borrows)

3. **Borrowing:**
   - Create new borrow records by selecting books and borrowers
   - Set custom due dates
   - Return books and automatically update availability
   - View borrow history, filter by status (active, returned, overdue)

## Developed For
- University DBMS Course Project 