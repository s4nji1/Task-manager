<?php
include('header.php');
include('condb.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all missions and users for selection
$missions = $pdo->query("SELECT id, nom FROM missions")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT id, nom FROM users WHERE id != {$_SESSION['user_id']}")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $missionId = $_POST['mission_id'];
    $userId = $_POST['user_id'];
    $permission = $_POST['permission'];

    // Insert share information into the shared_tasks table
    $stmt = $pdo->prepare("INSERT INTO shared_tasks (mission_id, user_partage_id, droit) VALUES (?, ?, ?)");
    $stmt->execute([$missionId, $userId, $permission]);

    // Fetch mission details to duplicate for the new user
    $missionDetails = $pdo->prepare("SELECT nom, description FROM missions WHERE id = ?");
    $missionDetails->execute([$missionId]);
    $missionData = $missionDetails->fetch(PDO::FETCH_ASSOC);

    if ($missionData) {
        // Insert a duplicate mission entry for the new user
        $insertMission = $pdo->prepare("INSERT INTO missions (nom, description, user_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $insertMission->execute([$missionData['nom'], $missionData['description'], $userId]);
        
        // Get the ID of the new mission
        $newMissionId = $pdo->lastInsertId();

        // Fetch tasks associated with the original mission
        $tasks = $pdo->prepare("SELECT nom, description, resultat, priorite, status FROM tasks WHERE mission_id = ?");
        $tasks->execute([$missionId]);
        $taskData = $tasks->fetchAll(PDO::FETCH_ASSOC);

        // Insert each task into the tasks table for the new mission
        foreach ($taskData as $task) {
            $insertTask = $pdo->prepare("INSERT INTO tasks (nom, description, resultat, priorite, status, user_id, mission_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $insertTask->execute([$task['nom'], $task['description'], $task['resultat'], $task['priorite'], $task['status'], $userId, $newMissionId]);
        }
    }

    echo "<div class='alert alert-success'>Mission and associated tasks shared successfully!</div>";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Share Mission</title>
</head>
<body>
    <div class="form-container">
        <h1 class="form-title">Share a Mission</h1>
        <form action="shareMission.php" method="POST">
            <div class="form-group">
                <label for="mission_id">Select Mission:</label>
                <select class="form-control" name="mission_id" required>
                <option value="">-- Select a mission --</option>
                    <?php foreach ($missions as $mission): ?>
                        <option value="<?= $mission['id'] ?>"><?= htmlspecialchars($mission['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="user_id">Share with User:</label>
                <select class="form-control" name="user_id" required>
                <option value="">-- Select a user --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="permission">Permission:</label>
                <select class="form-control" name="permission" required>
                <option value="">-- Select a permission --</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Share Mission</button>
        </form>
    </div>
</body>
</html>



<?php include('footer.php'); ?>
