<?php
include('header.php');
include('condb.php');

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_POST['update_operation'])) {
    $id = $_POST['id'];
    $operation = $_POST['operation'];

    $stmt = $pdo->prepare("UPDATE operations SET operation = ? WHERE id = ?");
    $stmt->execute([$operation, $id]);

    $logOperation = 'Operation updated: ' . $operation;
    $stmt = $pdo->prepare("INSERT INTO operations (user_id, operation, timestamp) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $logOperation, date('Y-m-d H:i:s')]);

    echo "<div class='alert alert-success'>Operation updated successfully!</div>";
}

if (isset($_POST['delete_operation'])) {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM operations WHERE id = ?");
    $stmt->execute([$id]);

    $logOperation = 'Operation deleted: ID ' . $id;
    $stmt = $pdo->prepare("INSERT INTO operations (user_id, operation, timestamp) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $logOperation, date('Y-m-d H:i:s')]);

    echo "<div class='alert alert-success'>Operation deleted successfully!</div>";
}

$stmt = $pdo->query("
    SELECT o.id, o.operation AS description, o.timestamp, u.nom AS user_name
    FROM operations o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.timestamp DESC
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
                    <td>
                        <button class="btn btn-info" data-toggle="modal" data-target="#viewModal<?= $record['id'] ?>">View</button>
                </tr>

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
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>
