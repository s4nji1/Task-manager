<?php
include('header.php');
include('condb.php');

// Start session if not already started
if (!isset($_SESSION)) {
    session_start();
}

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect to login page if the user is not an admin
    header('Location: login.php');
    exit();
}

// Fetch user operations from the database
$stmt = $pdo->query("SELECT o.operation, o.timestamp, u.nom FROM operations o JOIN users u ON o.user_id = u.id ORDER BY o.timestamp DESC");
$operations = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="content">
    <h2>User Operations</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>User</th>
                <th>Operation</th>
                <th>Date/Time</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($operations as $operation): ?>
                <tr>
                    <td><?= htmlspecialchars($operation['nom']) ?></td>
                    <td><?= htmlspecialchars($operation['operation']) ?></td>
                    <td><?= htmlspecialchars($operation['timestamp']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>
