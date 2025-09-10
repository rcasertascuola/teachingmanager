<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Adjust path for login.php based on the current script's location
    $path_to_login = (basename($_SERVER['PHP_SELF']) == 'index.php' && dirname($_SERVER['PHP_SELF']) == '/') ? 'login.php' : '../login.php';
    header("location: " . $path_to_login);
    exit;
}

// Function to check if a navigation link is active
function is_active($path) {
    $current_page = basename($_SERVER['PHP_SELF']);
    if (strpos($_SERVER['REQUEST_URI'], $path) !== false) {
        return 'active';
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionale Studio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .sub-nav {
            background-color: #f8f9fa;
            padding: 0.5rem 1rem;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 1rem;
        }
        /* Additional styles from dashboard might be needed */
        .feature-slot {
            min-height: 150px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard.php">Gestionale Studio</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="sub-nav">
        <ul class="nav nav-pills">
            <li class="nav-item"><a class="nav-link <?php echo is_active('/dashboard.php'); ?>" href="/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link <?php echo is_active('/udas/'); ?>" href="/udas/index.php"><i class="fas fa-book"></i> UDA</a></li>
            <li class="nav-item"><a class="nav-link <?php echo is_active('/modules/'); ?>" href="/modules/index.php"><i class="fas fa-puzzle-piece"></i> Modules</a></li>
            <li class="nav-item"><a class="nav-link <?php echo is_active('/lessons/'); ?>" href="/lessons/index.php"><i class="fas fa-chalkboard-teacher"></i> Lessons</a></li>
            <li class="nav-item"><a class="nav-link <?php echo is_active('/exercises/'); ?>" href="/exercises/index.php"><i class="fas fa-pencil-ruler"></i> Exercises</a></li>
            <li class="nav-item"><a class="nav-link <?php echo is_active('/conoscenze/'); ?>" href="/conoscenze/index.php"><i class="fas fa-lightbulb"></i> Knowledge</a></li>
            <li class="nav-item"><a class="nav-link <?php echo is_active('/abilita/'); ?>" href="/abilita/index.php"><i class="fas fa-tools"></i> Skills</a></li>
            <li class="nav-item"><a class="nav-link <?php echo is_active('/competenze/'); ?>" href="/competenze/index.php"><i class="fas fa-graduation-cap"></i> Competencies</a></li>
            <li class="nav-item"><a class="nav-link <?php echo is_active('/discipline/'); ?>" href="/discipline/index.php"><i class="fas fa-atom"></i> Disciplines</a></li>
            <li class="nav-item"><a class="nav-link <?php echo is_active('/synoptic_view.php'); ?>" href="/synoptic_view.php"><i class="fas fa-sitemap"></i> Synoptic View</a></li>
        </ul>
    </div>
