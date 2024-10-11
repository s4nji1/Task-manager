<?php
include('header.php');
include('condb.php');

// Handle task creation
if (isset($_POST['create_task'])) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $priorite = $_POST['priorite'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO tasks (nom, description, priorite, user_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nom, $description, $priorite, $user_id]);

    echo "<div class='alert alert-success'>Task created successfully!</div>";
}

// Handle task updates
if (isset($_POST['update_task'])) {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $priorite = $_POST['priorite'];

    $stmt = $pdo->prepare("UPDATE tasks SET nom = ?, description = ?, priorite = ? WHERE id = ?");
    $stmt->execute([$nom, $description, $priorite, $id]);

    echo "<div class='alert alert-success'>Task updated successfully!</div>";
}

// Handle task deletion
if (isset($_POST['delete_task'])) {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$id]);

    echo "<div class='alert alert-success'>Task deleted successfully!</div>";
}

// Fetch tasks
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
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Priority</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= $task['id'] ?></td>
                    <td><?= htmlspecialchars($task['nom']) ?></td>
                    <td><?= htmlspecialchars($task['description']) ?></td>
                    <td><?= htmlspecialchars($task['priorite']) ?></td>
                    <td>
                        <!-- View Task Modal Trigger -->
                        <button class="btn btn-info" data-toggle="modal" data-target="#viewModal<?= $task['id'] ?>">View</button>
                        <!-- Update Task Modal Trigger -->
                        <button class="btn btn-success" data-toggle="modal" data-target="#updateModal<?= $task['id'] ?>">Update</button>
                        <!-- Delete Task Form -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $task['id'] ?>">
                            <button type="submit" name="delete_task" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>

                <!-- View Task Modal -->
                <div class="modal fade" id="viewModal<?= $task['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel<?= $task['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewModalLabel<?= $task['id'] ?>">Task Details</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <h5>Name: <?= htmlspecialchars($task['nom']) ?></h5>
                                <p>Description: <?= htmlspecialchars($task['description']) ?></p>
                                <p>Priority: <?= htmlspecialchars($task['priorite']) ?></p>
                                <p>ID: <?= $task['id'] ?></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Update Task Modal -->
                <div class="modal fade" id="updateModal<?= $task['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel<?= $task['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form method="post">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateModalLabel<?= $task['id'] ?>">Update Task</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                    <div class="form-group">
                                        <label for="nom">Task Name</label>
                                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($task['nom']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Task Description</label>
                                        <textarea name="description" class="form-control"><?= htmlspecialchars($task['description']) ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="priorite">Priority</label>
                                        <select name="priorite" class="form-control">
                                            <option value="low" <?= $task['priorite'] === 'low' ? 'selected' : '' ?>>Low</option>
                                            <option value="medium" <?= $task['priorite'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                                            <option value="high" <?= $task['priorite'] === 'high' ? 'selected' : '' ?>>High</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" name="update_task" class="btn btn-primary">Update Task</button>
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
