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

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE mission_id = ?");
    $stmt->execute([$id]);
    $taskCount = $stmt->fetchColumn();

    if ($taskCount > 0) {
        echo "<div class='alert alert-danger'>Cannot delete mission with associated tasks. Please remove the tasks first.</div>";
    } else {
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
                        <button class="btn btn-info" data-toggle="modal" data-target="#viewModal<?= $mission['id'] ?>">View</button>
                        <button class="btn btn-success" data-toggle="modal" data-target="#updateModal<?= $mission['id'] ?>">Update</button>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $mission['id'] ?>">
                            <button type="submit" name="delete_mission" class="btn btn-danger">Delete</button>
                        </form>
                        <a href="task.php?mission_id=<?= $mission['id'] ?>" class="btn btn-warning">Manage Tasks</a>
                    </td>
                </tr>
                <!-- Existing modals remain unchanged -->
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>
