<?php
include('header.php');
include('condb.php');

// Ensure session is started and user is logged in
if (!isset($_SESSION)) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle mission creation
if (isset($_POST['create_mission'])) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];

    // Insert mission into the database
    $stmt = $pdo->prepare("INSERT INTO missions (nom, description, user_id) VALUES (?, ?, ?)");
    $stmt->execute([$nom, $description, $user_id]);

    // Log operation
    $operation = 'Mission created: ' . $nom;
    $stmt = $pdo->prepare("INSERT INTO operations (user_id, operation, timestamp) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $operation, date('Y-m-d H:i:s')]);

    echo "<div class='alert alert-success'>Mission created successfully!</div>";
}

// Handle mission updates
if (isset($_POST['update_mission'])) {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $description = $_POST['description'];

    // Update mission in the database
    $stmt = $pdo->prepare("UPDATE missions SET nom = ?, description = ? WHERE id = ?");
    $stmt->execute([$nom, $description, $id]);

    // Log operation
    $operation = 'Mission updated: ' . $nom;
    $stmt = $pdo->prepare("INSERT INTO operations (user_id, operation, timestamp) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $operation, date('Y-m-d H:i:s')]);

    echo "<div class='alert alert-success'>Mission updated successfully!</div>";
}

// Handle mission deletion
if (isset($_POST['delete_mission'])) {
    $id = $_POST['id'];

    // Check if there are associated tasks
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE mission_id = ?");
    $stmt->execute([$id]);
    $taskCount = $stmt->fetchColumn();

    if ($taskCount > 0) {
        echo "<div class='alert alert-danger'>Cannot delete mission with associated tasks. Please remove the tasks first.</div>";
    } else {
        // Delete mission from the database
        $stmt = $pdo->prepare("DELETE FROM missions WHERE id = ?");
        $stmt->execute([$id]);

        // Log operation
        $operation = 'Mission deleted: ' . $id; // Use mission ID or name for clarity
        $stmt = $pdo->prepare("INSERT INTO operations (user_id, operation, timestamp) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $operation, date('Y-m-d H:i:s')]);

        echo "<div class='alert alert-success'>Mission deleted successfully!</div>";
    }
}

// Fetch all missions of the logged-in user
$stmt = $pdo->prepare("SELECT * FROM missions WHERE user_id = ?");
$stmt->execute([$user_id]);
$missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2>Manage Missions</h2>

    <form method="post">
        <div class="form-group">
            <label for="nom">Mission Name</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Mission Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <button type="submit" name="create_mission" class="btn btn-primary">Create Mission</button>
    </form>

    <h3>Your Missions</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($missions as $mission): ?>
                <tr>
                    <td><?= $mission['id'] ?></td>
                    <td><?= htmlspecialchars($mission['nom']) ?></td>
                    <td><?= htmlspecialchars($mission['description']) ?></td>
                    <td>
                        <button class="btn btn-info" data-toggle="modal"
                            data-target="#viewModal<?= $mission['id'] ?>">View</button>
                        <button class="btn btn-success" data-toggle="modal"
                            data-target="#updateModal<?= $mission['id'] ?>">Update</button>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $mission['id'] ?>">
                            <button type="submit" name="delete_mission" class="btn btn-danger">Delete</button>
                        </form>
                        <a href="task.php?mission_id=<?= $mission['id'] ?>" class="btn btn-warning">Manage Tasks</a>
                    </td>
                </tr>
                <!-- View Modal -->
                <div class="modal fade" id="viewModal<?= $mission['id'] ?>" tabindex="-1" role="dialog"
                    aria-labelledby="viewModalLabel<?= $mission['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewModalLabel<?= $mission['id'] ?>">Mission Details</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Mission Name:</strong> <?= htmlspecialchars($mission['nom']) ?></p>
                                <p><strong>Description:</strong> <?= htmlspecialchars($mission['description']) ?></p>
                                <p><strong>Created By:</strong> <?= htmlspecialchars($user_id) ?></p>
                                <!-- Adjust if you have a username or name field -->
                                <p><strong>Date Created:</strong> <?= htmlspecialchars($mission['created_at']) ?></p>
                                <!-- Adjust if you have a date field -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Update Modal -->
                <div class="modal fade" id="updateModal<?= $mission['id'] ?>" tabindex="-1" role="dialog"
                    aria-labelledby="updateModalLabel<?= $mission['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateModalLabel<?= $mission['id'] ?>">Update Mission</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form method="post" action="mission.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="id" value="<?= $mission['id'] ?>">
                                    <div class="form-group">
                                        <label for="nom">Mission Name</label>
                                        <input type="text" name="nom" class="form-control"
                                            value="<?= htmlspecialchars($mission['nom']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Mission Description</label>
                                        <textarea name="description"
                                            class="form-control"><?= htmlspecialchars($mission['description']) ?></textarea>
                                    </div>
                                    <button type="submit" name="update_mission" class="btn btn-primary">Update
                                        Mission</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Delete Confirmation Modal -->
                <div class="modal fade" id="deleteModal<?= $mission['id'] ?>" tabindex="-1" role="dialog"
                    aria-labelledby="deleteModalLabel<?= $mission['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel<?= $mission['id'] ?>">Delete Mission</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete this mission?</p>
                                <form method="post" action="mission.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="id" value="<?= $mission['id'] ?>">
                                    <button type="submit" name="delete_mission" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>