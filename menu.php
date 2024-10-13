<?php
// Ensure session is started
if (!isset($_SESSION)) {
    session_start();
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
?>

<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column">
        <?php if ($isLoggedIn): ?>
            <li class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="nav-icon fas fa-home"></i>
                    <p>Dashboard</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="mission.php" class="nav-link">
                    <i class="nav-icon fas fa-tasks"></i>
                    <p>Missions</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="task.php" class="nav-link">
                    <i class="nav-icon fas fa-list"></i>
                    <p>Tasks</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="LinkTaskToMission.php" class="nav-link">
                    <i class="nav-icon fas fa-link"></i>
                    <p>Link task to mission</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="user.php" class="nav-link">
                    <i class="nav-icon fas fa-user"></i>
                    <p>User Profile</p>
                </a>
            </li>
            <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <a href="operation.php" class="nav-link">
                        <i class="nav-icon fas fa-history"></i>
                        <p>User Operations</p>
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a href="logout.php" class="nav-link">
                    <i class="nav-icon fas fa-sign-out-alt"></i>
                    <p>Logout</p>
                </a>
            </li>
        <?php else: ?>
            <!-- Show the Login and Register buttons if the user is not logged in -->
            <li class="nav-item">
                <a href="login.php" class="nav-link">
                    <i class="nav-icon fas fa-sign-in-alt"></i>
                    <p>Login</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="register.php" class="nav-link">
                    <i class="nav-icon fas fa-user-plus"></i>
                    <p>Register</p>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
