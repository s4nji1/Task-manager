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

// Get the mission ID from the query parameter
$mission_id = isset($_GET['mission_id']) ? $_GET['mission_id'] : null;

// Handle task creation
if (isset($_POST['create_task']) && $mission_id) {
    $task_name = $_POST['task_name'];
    $description = $_POST['description'];

    // Insert new task
    $stmt = $pdo->prepare("INSERT INTO tasks (nom, description, mission_id, user_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$task_name, $description, $mission_id, $user_id]);

    // Log operation
    $operation = 'Task created: ' . $task_name;
    $stmt = $pdo->prepare("INSERT INTO operations (user_id, operation, timestamp) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $operation, date('Y-m-d H:i:s')]);

    echo "<div class='alert alert-success'>Task created successfully!</div>";
}

// Handle task deletion
if (isset($_POST['delete_task'])) {
    $task_id = $_POST['id'];

    // Delete task from the database
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);

    // Log operation
    $operation = 'Task deleted: ' . $task_id; // You can include the task name if needed
    $stmt = $pdo->prepare("INSERT INTO operations (user_id, operation, timestamp) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $operation, date('Y-m-d H:i:s')]);

    echo "<div class='alert alert-success'>Task deleted successfully!</div>";
}

// Handle task update
if (isset($_POST['update_task'])) {
    $task_id = $_POST['id'];
    $task_name = $_POST['task_name'];
    $description = $_POST['description'];

    // Update task in the database
    $stmt = $pdo->prepare("UPDATE tasks SET nom = ?, description = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_name, $description, $task_id, $user_id]);

    // Log operation
    $operation = 'Task updated: ' . $task_name;
    $stmt = $pdo->prepare("INSERT INTO operations (user_id, operation, timestamp) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $operation, date('Y-m-d H:i:s')]);

    echo "<div class='alert alert-success'>Task updated successfully!</div>";
}

// Fetch all tasks for the specified mission
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE mission_id = ?");
$stmt->execute([$mission_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <h2>Manage Tasks for Mission ID: <?= htmlspecialchars($mission_id) ?></h2>

    <form method="post">
        <div class="form-group">
            <label for="task_name">Task Name</label>
            <input type="text" name="task_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Task Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <button type="submit" name="create_task" class="btn btn-primary">Create Task</button>
    </form>

    <h3>Tasks for Mission</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($tasks) > 0): ?>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?= $task['id'] ?></td>
                        <td><?= htmlspecialchars($task['nom']) ?></td>
                        <td><?= htmlspecialchars($task['description']) ?></td>
                        <td>
                            <?php
                            // Define color classes for priority
                            $priorityClass = '';
                            if ($task['priorite'] === 'Low') {
                                $priorityClass = 'bg-info text-white';
                            } elseif ($task['priorite'] === 'Medium') {
                                $priorityClass = 'bg-warning text-dark';
                            } elseif ($task['priorite'] === 'High') {
                                $priorityClass = 'bg-danger text-white';
                            }
                            ?>

                            <span class="badge <?= $priorityClass; ?>">
                                <?= ucfirst($task['priorite']); ?>
                            </span>
                        </td>

                        <td>
                            <?php
                            // Define color classes for status
                            $statusClass = '';
                            if ($task['status'] === 'in progress') {
                                $statusClass = 'bg-warning text-dark';
                            } elseif ($task['status'] === 'completed') {
                                $statusClass = 'bg-success text-white';
                            } elseif ($task['status'] === 'impossible') {
                                $statusClass = 'bg-danger text-white';
                            } elseif ($task['status'] === 'postponed') {
                                $statusClass = 'bg-secondary text-white';
                            }
                            ?>

                            <span class="badge <?= $statusClass; ?>">
                                <?= ucfirst($task['status']); ?>
                            </span>
                        </td>
                        <td>
                            <!-- View Task Button -->
                            <button class="btn btn-info" data-toggle="modal"
                                data-target="#viewModal<?= $task['id'] ?>">View</button>

                            <!-- Update Task Modal Trigger -->
                            <button class="btn btn-success" data-toggle="modal"
                                data-target="#updateModal<?= $task['id'] ?>">Update</button>

                            <!-- Delete Task Form -->
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                <button type="submit" name="delete_task" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <!-- View Task Modal -->
                    <div class="modal fade" id="viewModal<?= $task['id'] ?>" tabindex="-1" role="dialog"
                        aria-labelledby="viewModalLabel<?= $task['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="viewModalLabel<?= $task['id'] ?>">Task Details</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Name:</strong> <?= htmlspecialchars($task['nom']) ?></p>
                                    <p><strong>Description:</strong> <?= htmlspecialchars($task['description']) ?></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Task Modal -->
                    <div class="modal fade" id="updateModal<?= $task['id'] ?>" tabindex="-1" role="dialog"
                        aria-labelledby="updateModalLabel<?= $task['id'] ?>" aria-hidden="true">
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
                                            <label for="task_name">Task Name</label>
                                            <input type="text" name="task_name" class="form-control"
                                                value="<?= htmlspecialchars($task['nom']) ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="description">Task Description</label>
                                            <textarea name="description"
                                                class="form-control"><?= htmlspecialchars($task['description']) ?></textarea>
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
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No tasks found for this mission.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>