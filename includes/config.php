<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fgdcw_bookbridgee";

// Create connection
// Object ek complex variable hai jo properties aur methods hold karta hai. Difference: variable me ek value hoti hai, object me multiple values/functions bundled hote hain.
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Karachi'); 
$conn->query("SET time_zone = '+05:00'");
//Time zone offset ka use karne se aapko different time zones mein date aur time ki calculations karne mein madad milti hai.

// Check if database exists, if not create it
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
} /*Agar query fail → script yahi ruk jaayega
$conn->error → MySQL ka detailed error message print karega*/

// Select the database ab jo change hoga esi mein hoga table creation etc
$conn->select_db($dbname);

// Create Users table with unique_id field
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    unique_id VARCHAR(20) UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('librarian', 'student', 'faculty') NOT NULL,
    department VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
// ENUM Ensures only valid values are stored without relying solely on PHP validation.
//varchar:Use: Short-to-medium text (generally up to 255 characters).
//text Use: Long text (can store thousands of characters).
//MySQL ka datatype jo date + time store karta hai.
//Format: YYYY-MM-DD HH:MM:SS
//Example: 2025-08-14 12:45:30
if ($conn->query($sql) !== TRUE) {
    die("Error creating users table: " . $conn->error);
}
// bolta hay sql query ko run kro jo sql mein hay
// Add unique_id column if it doesn't exist
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS unique_id VARCHAR(20) UNIQUE AFTER id";
$conn->query($sql);


// Create Books table with updated structure
$sql = "CREATE TABLE IF NOT EXISTS books (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    book_name VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    book_no VARCHAR(50) UNIQUE,
    publisher VARCHAR(100),
    category VARCHAR(50),
    available_quantity INT(11) NOT NULL DEFAULT 0,
    total_quantity INT(11) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP 
)";


//DEFAULT CURRENT_TIMESTAMP → agar insert query me value na di ho → current date & time automatically insert ho jaye.
if ($conn->query($sql) !== TRUE) {
    die("Error creating books table: " . $conn->error);
}
//Purpose: last executed query ka error message return karta ha
// Create E-books table
$sql = "CREATE TABLE IF NOT EXISTS ebooks (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,                               
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    type VARCHAR(50),
    file_path VARCHAR(255) NOT NULL,
    file_size VARCHAR(20),
    file_type VARCHAR(20),
    description TEXT,
    cover_image VARCHAR(255),
    uploaded_by INT(11),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
)";
if ($conn->query($sql) !== TRUE) {
    die("Error creating ebooks table: " . $conn->error);
}

// Add type column if it doesn't exist
$sql = "ALTER TABLE ebooks ADD COLUMN IF NOT EXISTS type VARCHAR(50) AFTER category";
$conn->query($sql);

// Add cover_image column if it doesn't exist  
$sql = "ALTER TABLE ebooks ADD COLUMN IF NOT EXISTS cover_image VARCHAR(255) AFTER description";
$conn->query($sql);

// Create Issued Books table
$sql = "CREATE TABLE IF NOT EXISTS issued_books (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    book_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    issue_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_date DATE NOT NULL,
    actual_return_date DATE,
    status ENUM('issued', 'returned', 'overdue') NOT NULL DEFAULT 'issued',
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
//Decimal Matlab maximum value: 99999999.99
if ($conn->query($sql) !== TRUE) {
    die("Error creating issued_books table: " . $conn->error);
}

// Create Book Requests table
$sql = "CREATE TABLE IF NOT EXISTS book_requests (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    book_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) !== TRUE) {
    die("Error creating book_requests table: " . $conn->error);
}

// Create Reservation Requests table (NEW)
$sql = "CREATE TABLE IF NOT EXISTS reservation_requests (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    book_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) !== TRUE) {
    die("Error creating reservation_requests table: " . $conn->error);
}

// Create Book Reservations table
$sql = "CREATE TABLE IF NOT EXISTS book_reservations (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    book_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    reservation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'fulfilled', 'cancelled', 'expired') NOT NULL DEFAULT 'active',
    priority_number INT(11) NOT NULL,
    expires_at DATETIME NOT NULL,
    notified_at DATETIME NULL,
    notes TEXT,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_book_priority (book_id, priority_number),
    INDEX idx_status_expires (status, expires_at)
)";
//index :SELECT * FROM requests WHERE status='pending' AND expires_at < NOW();
if ($conn->query($sql) !== TRUE) {
    die("Error creating book_reservations table: " . $conn->error);
}

// Create Login Attempts table for security
$sql = "CREATE TABLE IF NOT EXISTS login_attempts (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE,
    INDEX idx_identifier_time (identifier, attempt_time),
    INDEX idx_ip_time (ip_address, attempt_time)
)";
//SELECT * FROM login_attempts WHERE identifier='Iqra' ORDER BY attempt_time DESC
if ($conn->query($sql) !== TRUE) {
    die("Error creating login_attempts table: " . $conn->error);
}

// Create Fines table
$sql = "CREATE TABLE IF NOT EXISTS fines (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    issued_book_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    reason TEXT,
    status ENUM('pending', 'paid') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (issued_book_id) REFERENCES issued_books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
//Cascade ka matlab hai: agar parent table ka record delete ho jaye → child table ke related records automatically delete ho jaye
//Matlab: issued_book_id ke values sirf issued_books.id ke existing values ho sakte hain
//فہرست میں درج (fehrist mein darj)
if ($conn->query($sql) !== TRUE) {
    die("Error creating fines table: " . $conn->error);
}

// Create Payments table
$sql = "CREATE TABLE IF NOT EXISTS payments (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    fine_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(50),
    receipt_number VARCHAR(50),
    transaction_id VARCHAR(100),
    payment_details TEXT,
    FOREIGN KEY (fine_id) REFERENCES fines(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) !== TRUE) {
    die("Error creating payments table: " . $conn->error);
}

// Create Notifications table
$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) !== TRUE) {
    die("Error creating notifications table: " . $conn->error);
}

// Function to generate unique ID
function generateUniqueId($conn, $role) {
    $prefix = '';
    switch($role) {
        case 'student':
            $prefix = 'STU';
            break;
        case 'faculty':
            $prefix = 'FAC';
            break;
        case 'librarian':
            $prefix = 'LIB';
            break;
    }
    
    do {
        $randomNumber = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $uniqueId = $prefix . $randomNumber;
        
        // Check if this ID already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE unique_id = ?");
        $stmt->bind_param("s", $uniqueId);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);
    
    return $uniqueId;
}
/*$stmt PHP mein ek variable hai jo prepared statement ko hold karta hai

Prepared statement → SQL query ka template jisme values baad mein safely bind hoti hain

Purpose: SQL Injection se bachna aur queries fast run karna*/
// Function to check login attempts and implement security
function checkLoginAttempts($conn, $identifier, $ipAddress) {
    // Clean old attempts (older than 60 seconds)
    $cleanupSql = "DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 60 MINUTE)";
    $conn->query($cleanupSql);
    
    // Check failed attempts in last 60 seconds
    $stmt = $conn->prepare("
        SELECT COUNT(*) as failed_attempts, 
               MAX(attempt_time) as last_attempt
        FROM login_attempts 
        WHERE (identifier = ? OR ip_address = ?) 
        AND success = FALSE 
        AND attempt_time > DATE_SUB(NOW(), INTERVAL 60 MINUTE)
    ");
    $stmt->bind_param("ss", $identifier, $ipAddress);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    //Data is stored as pairs, where a unique "key" identifies and allows access to its corresponding "value."
    if ($row['failed_attempts'] >= 3) {
        $lastAttempt = new DateTime($row['last_attempt']);
        $now = new DateTime();
        $timeDiff = $now->getTimestamp() - $lastAttempt->getTimestamp();
        $blockDuration = 60 * 60; // 60 seconds in seconds
        
        if ($timeDiff < $blockDuration) {
            return array(
                'blocked' => true,
                'remaining_time' => ceil(($blockDuration - $timeDiff) / 60), // ceil Purpose: decimal number ko next highest integer me round karna
                'message' => 'Too many failed login attempts. Please wait ' . ceil(($blockDuration - $timeDiff) / 60) . ' seconds before trying again.'
            );
        }
    }
    
    return array('blocked' => false);
}


// Function to record login attempt
function recordLoginAttempt($conn, $identifier, $ipAddress, $success) {
    //Purpose: har login attempt ko database me record karna
    $stmt = $conn->prepare("INSERT INTO login_attempts (identifier, ip_address, success) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $identifier, $ipAddress, $success);
    $stmt->execute();
}

// Function to get user's IP address
function getUserIpAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Calculate fine amount based on days overdue
function calculateFine($dueDate, $returnDate, $finePerDay = 100.00, $userRole = 'student') {
    // Faculty members are exempt from fines
    if ($userRole === 'faculty') {
        return 0;
    }
    $due = new DateTime($dueDate);
    $return = new DateTime($returnDate);
    $diff = $return->diff($due);
    
    if ($return > $due) {
        $daysOverdue = $diff->days;
        return $daysOverdue * $finePerDay;
    }
    
    return 0;
}



?>