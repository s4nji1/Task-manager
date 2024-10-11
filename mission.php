<?php
include('header.php');
include('condb.php');

// Ensure session is started and user is logged in
if (!isset($_SESSION)) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle mission creation
if (isset($_POST['create_mission'])) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];

    // Insert new mission
    $stmt = $pdo->prepare("INSERT INTO missions (nom, description, user_id) VALUES (?, ?, ?)");
    $stmt->execute([$nom, $description, $user_id]);

    echo "<div class='alert alert-success'>Mission created successfully!</div>";
}

// Handle mission updates
if (isset($_POST['update_mission'])) {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("UPDATE missions SET nom = ?, description = ? WHERE id = ?");
    $stmt->execute([$nom, $description, $id]);

    echo "<div class='alert alert-success'>Mission updated successfully!</div>";
}

// Handle mission deletion
if (isset($_POST['delete_mission'])) {
    $id = $_POST['id'];

    // Check if there are any tasks associated with this mission
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE mission_id = ?");
    $stmt->execute([$id]);
    $taskCount = $stmt->fetchColumn();

    if ($taskCount > 0) {
        // Inform the user that tasks are associated with this mission
        echo "<div class='alert alert-danger'>Cannot delete mission with associated tasks. Please remove the tasks first.</div>";
    } else {
        // Proceed to delete the mission
        $stmt = $pdo->prepare("DELETE FROM missions WHERE id = ?");
        $stmt->execute([$id]);
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
                        <!-- View Mission Button -->
                        <button class="btn btn-info" data-toggle="modal" data-target="#viewModal<?= $mission['id'] ?>">View</button>
                        
                        <!-- Update Mission Modal Trigger -->
                        <button class="btn btn-success" data-toggle="modal" data-target="#updateModal<?= $mission['id'] ?>">Update</button>
                        
                        <!-- Delete Mission Form -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $mission['id'] ?>">
                            <button type="submit" name="delete_mission" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>

                <!-- View Mission Modal -->
                <div class="modal fade" id="viewModal<?= $mission['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel<?= $mission['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewModalLabel<?= $mission['id'] ?>">Mission Details</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Name:</strong> <?= htmlspecialchars($mission['nom']) ?></p>
                                <p><strong>Description:</strong> <?= htmlspecialchars($mission['description']) ?></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Update Mission Modal -->
                <div class="modal fade" id="updateModal<?= $mission['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel<?= $mission['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form method="post">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateModalLabel<?= $mission['id'] ?>">Update Mission</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?= $mission['id'] ?>">
                                    <div class="form-group">
                                        <label for="nom">Mission Name</label>
                                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($mission['nom']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Mission Description</label>
                                        <textarea name="description" class="form-control"><?= htmlspecialchars($mission['description']) ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" name="update_mission" class="btn btn-primary">Update Mission</button>
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
