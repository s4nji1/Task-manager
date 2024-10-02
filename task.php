<?php
include('header.php');
include('condb.php');

if (isset($_POST['create_task'])) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $priorite = $_POST['priorite'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO tasks (nom, description, priorite, user_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nom, $description, $priorite, $user_id]);

    echo "Task created successfully!";
}

$tasks = $pdo->query("SELECT * FROM tasks WHERE user_id = ".$_SESSION['user_id'])->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2>Manage Tasks</h2>

    <form method="post">
        <div class="form-group">
            <label for="nom">Task Name</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Task Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label for="priorite">Priority</label>
            <select name="priorite" class="form-control">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
        </div>
        <button type="submit" name="create_task" class="btn btn-primary">Create Task</button>
    </form>

    <h3>Your Tasks</h3>
    <ul>
        <?php foreach ($tasks as $task): ?>
            <li><?= $task['nom'] ?> - <?= $task['priorite'] ?></li>
        <?php endforeach; ?>
    </ul>
</div>

<?php include('footer.php'); ?>
