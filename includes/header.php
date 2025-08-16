<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
/*Yeh check karta hai ke PHP session pehle se start hua hai ya nahi. Agar nahi hua (PHP_SESSION_NONE), toh session_start() call karke session shuru karta hai.*/

// Include database connection
include_once __DIR__ . '/config.php';
include_once __DIR__ . '/functions.php';

/*config.php mein database connection settings hoti hain (host, user, password, db name, etc.).
functions.php mein helper functions hote hain jo puray project mein use hote hain.*/

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
// Get current page for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

/*$_SERVER['PHP_SELF'] current running file ka path deta hai.
basename() se sirf file ka naam milta hai (jaise dashboard.php).
Yeh baad mein menu highlight karne ke liye use hota hai*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FGDCW Bookbridge system</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="icon" type="image/png" href="../uploads/assests/book.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!--Select2 ek JavaScript library hai jo dropdowns ko searchable aur stylish banati hai.-->
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="top-navbar">
        <div class="navbar-container">
            <div class="navbar-left">
                <a href="dashboard.php" class="navbar-logo">
                    <img src="../uploads/assests/book.png" alt="Library Logo" class="logo-image">
                    <span class="navbar-title">BookBridge</span>
                </a>
            </div>
            
            <div class="navbar-right">
                <!--bootsrap button navbar-btn-->
                <a href="profile.php" class="navbar-btn profile-btn">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </a>
                
                <a href="../logout.php" class="navbar-btn logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            
            <!-- Librarian Sidebar -->
            <div class="sidebar-menu">
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'student' || $_SESSION['role'] === 'faculty')): ?>
                    <a href="dashboard.php" class="sidebar-menu-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                        <!--? 'active' : '' → Ternary operator

                        Agar condition true → 'active' print karo (CSS class)

                        Agar false → empty string '' (kuch add na karo)-->
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="sidebar-menu-label">Dashboard</span>
                    </a>
                    <a href="catalog.php" class="sidebar-menu-item <?php echo $currentPage == 'books.php' ? 'active' : ''; ?>">
                        <i class="fas fa-book"></i>
                        <span class="sidebar-menu-label">Books</span>
                    </a>
                    <a href="reservations.php" class="sidebar-menu-item <?php echo $currentPage == 'reservations.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-check"></i>
                        <span class="sidebar-menu-label">Reservations</span>
                    </a>
                    <a href="ebooks.php" class="sidebar-menu-item <?php echo $currentPage == 'ebooks.php' ? 'active' : ''; ?>">
                        <i class="fas fa-file-pdf"></i>
                        <span class="sidebar-menu-label">E-Books</span>
                    </a>
                    <a href="requests.php" class="sidebar-menu-item <?php echo $currentPage == 'requests.php' ? 'active' : ''; ?>">
                        <i class="fas fa-bookmark"></i>
                        <span class="sidebar-menu-label">My Requests</span>
                    </a>
                    <a href="returns.php" class="sidebar-menu-item <?php echo $currentPage == 'returns.php' ? 'active' : ''; ?>">
                        <i class="fas fa-undo"></i>
                        <span class="sidebar-menu-label">My Returns</span>
                    </a>
                    <a href="fines.php" class="sidebar-menu-item <?php echo $currentPage == 'fines.php' ? 'active' : ''; ?>">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="sidebar-menu-label">My Fines</span>
                    </a>
                    <a href="notifications.php" class="sidebar-menu-item <?php echo $currentPage == 'notifications.php' ? 'active' : ''; ?>">
                        <i class="fas fa-bell"></i>
                        <span class="sidebar-menu-label">Notifications</span>
                    </a>
                    <a href="feedback.php" class="sidebar-menu-item <?php echo $currentPage == 'feedback.php' ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i>
                        <span class="sidebar-menu-label">Feedback</span>
                    </a>
                <?php else: ?>
                    <!-- Librarian Sidebar -->
                    <a href="dashboard.php" class="sidebar-menu-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span class="sidebar-menu-label">Dashboard</span>
                    </a>
                    <a href="books.php" class="sidebar-menu-item <?php echo $currentPage == 'books.php' ? 'active' : ''; ?>">
                        <i class="fas fa-book"></i>
                        <span class="sidebar-menu-label">Books</span>
                    </a>
                    <a href="e-books.php" class="sidebar-menu-item <?php echo $currentPage == 'e-books.php' ? 'active' : ''; ?>">
                        <i class="fas fa-file-pdf"></i>
                        <span class="sidebar-menu-label">E-Books</span>
                    </a>
                    <a href="users.php" class="sidebar-menu-item <?php echo $currentPage == 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span class="sidebar-menu-label">Users</span>
                    </a>
                    <a href="issue_book.php" class="sidebar-menu-item <?php echo $currentPage == 'issue_book.php' ? 'active' : ''; ?>">
                        <i class="fas fa-book-open"></i>
                        <span class="sidebar-menu-label">Book Issue</span>
                    </a>
                    <a href="requests.php" class="sidebar-menu-item <?php echo $currentPage == 'requests.php' ? 'active' : ''; ?>">
                        <i class="fas fa-bookmark"></i>
                        <span class="sidebar-menu-label">Book Requests</span>
                    </a>
                    <a href="returns.php" class="sidebar-menu-item <?php echo $currentPage == 'returns.php' ? 'active' : ''; ?>">
                        <i class="fas fa-undo"></i>
                        <span class="sidebar-menu-label">Book Returns</span>
                    </a>
                    <a href="fines.php" class="sidebar-menu-item <?php echo $currentPage == 'fines.php' ? 'active' : ''; ?>">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="sidebar-menu-label">Fines</span>
                    </a>
                    <a href="notifications.php" class="sidebar-menu-item <?php echo $currentPage == 'notifications.php' ? 'active' : ''; ?>">
                        <i class="fas fa-bell"></i>
                        <span class="sidebar-menu-label">Notifications</span>
                    </a>
                    <a href="weed_off_books.php" class="sidebar-menu-item <?php echo $currentPage == 'weed_off_books.php' ? 'active' : ''; ?>">
                        <i class="fas fa-trash-alt"></i>
                        <span class="sidebar-menu-label">Weed Off Books</span>
                    </a>
                    <a href="clearance_report.php" class="sidebar-menu-item <?php echo $currentPage == 'clearance_report.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> 
                    <span class="sidebar-menu-label">Clearance Report</span>
                    </a>

                    <a href="feedback.php" class="sidebar-menu-item <?php echo $currentPage == 'feedback.php' ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i>
                        <span class="sidebar-menu-label">Feedback</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Main Content Area -->
            <div class="content">