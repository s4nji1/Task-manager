<?php
include('header.php');
include('condb.php');

// Fetch all missions for the logged-in user
$missions = $pdo->query("SELECT * FROM missions WHERE user_id = ".$_SESSION['user_id'])->fetchAll(PDO::FETCH_ASSOC);

// Fetch all tasks for the logged-in user
$tasks = $pdo->query("SELECT * FROM tasks WHERE user_id = ".$_SESSION['user_id'])->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['submit'])) {
    $task_id = $_POST['task_id'];
    $mission_id = $_POST['mission_id'];
    
    // Update the task with the mission ID
    $stmt = $pdo->prepare("UPDATE tasks SET mission_id = ? WHERE id = ?");
    $stmt->execute([$mission_id, $task_id]);

    echo "Task linked to mission successfully!";
}

?>

<div class="content">
    <h2>Link Task to Mission</h2>
    <form method="post">
        <div class="form-group">
            <label for="task_id">Select Task</label>
            <select name="task_id" class="form-control" required>
                <option value="">-- Select a Task --</option>
                <?php foreach ($tasks as $task): ?>
                    <option value="<?= $task['id'] ?>"><?= htmlspecialchars($task['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="mission_id">Select Mission</label>
            <select name="mission_id" class="form-control" required>
                <option value="">-- Select a Mission --</option>
                <?php foreach ($missions as $mission): ?>
                    <option value="<?= $mission['id'] ?>"><?= htmlspecialchars  ($mission['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Link Task</button>
    </form>
</div>

<?php include('footer.php'); ?>
