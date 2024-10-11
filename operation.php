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

// Handle operation updates
if (isset($_POST['update_operation'])) {
    $id = $_POST['id'];
    $operation = $_POST['operation'];

    $stmt = $pdo->prepare("UPDATE operations SET operation = ? WHERE id = ?");
    $stmt->execute([$operation, $id]);

    echo "<div class='alert alert-success'>Operation updated successfully!</div>";
}

// Handle operation deletion
if (isset($_POST['delete_operation'])) {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM operations WHERE id = ?");
    $stmt->execute([$id]);

    echo "<div class='alert alert-success'>Operation deleted successfully!</div>";
}

// Fetch all operations, tasks, and missions from the database
$stmt = $pdo->query("
    SELECT 'operation' AS type, o.id, o.operation AS description, o.timestamp, u.nom AS user_name
    FROM operations o
    JOIN users u ON o.user_id = u.id
    UNION ALL
    SELECT 'task' AS type, t.id, t.description, t.created_at AS timestamp, u.nom AS user_name
    FROM tasks t
    JOIN users u ON t.user_id = u.id
    UNION ALL
    SELECT 'mission' AS type, m.id, m.description, m.created_at AS timestamp, u.nom AS user_name
    FROM missions m
    JOIN users u ON m.user_id = u.id
    ORDER BY timestamp DESC
");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2>Operations Log</h2><br><br>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Description</th>
                <th>Date/Time</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td><?= $record['id'] ?></td>
                    <td><?= htmlspecialchars($record['user_name']) ?></td>
                    <td><?= htmlspecialchars($record['description']) ?></td>
                    <td><?= htmlspecialchars($record['timestamp']) ?></td>
                    <td><?= htmlspecialchars($record['type']) ?></td>
                    <td>
                        <!-- View Button -->
                        <button class="btn btn-info" data-toggle="modal" data-target="#viewModal<?= $record['id'] ?>">View</button>
                        <!-- Update Modal Trigger -->
                        <button class="btn btn-success" data-toggle="modal" data-target="#updateModal<?= $record['id'] ?>">Update</button>
                        <!-- Delete Form -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $record['id'] ?>">
                            <button type="submit" name="delete_operation" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>

                <!-- View Modal -->
                <div class="modal fade" id="viewModal<?= $record['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel<?= $record['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewModalLabel<?= $record['id'] ?>">View Record</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><strong>User:</strong> <?= htmlspecialchars($record['user_name']) ?></p>
                                <p><strong>Description:</strong> <?= htmlspecialchars($record['description']) ?></p>
                                <p><strong>Date/Time:</strong> <?= htmlspecialchars($record['timestamp']) ?></p>
                                <p><strong>Type:</strong> <?= htmlspecialchars($record['type']) ?></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Update Modal -->
                <div class="modal fade" id="updateModal<?= $record['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel<?= $record['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form method="post">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateModalLabel<?= $record['id'] ?>">Update Record</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?= $record['id'] ?>">
                                    <div class="form-group">
                                        <label for="operation">Description</label>
                                        <input type="text" name="operation" class="form-control" value="<?= htmlspecialchars($record['description']) ?>" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" name="update_operation" class="btn btn-primary">Update Record</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>
