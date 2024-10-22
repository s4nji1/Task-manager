<?php
include('header.php');
include('condb.php');

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<div class="content">
    <h2>User Profile</h2>
    <table class="table">
        <tr>
            <th>Name:</th>
            <td><?= $user['nom']; ?></td>
        </tr>
        <tr>
            <th>Email:</th>
            <td><?= $user['email']; ?></td>
        </tr>
        <tr>
            <th>Status:</th>
            <td><?= ucfirst($user['etat']); ?></td>
        </tr>
        <tr>
            <th>Role:</th>
            <td><?= ucfirst($user['droit']); ?></td>
        </tr>
    </table>
    <div class="col-12 text-center mt-3">
        <a href="mission.php" class="btn btn-primary mt-5 ">Manage Missions and Tasks</a>
    </div>
</div>

<?php include('footer.php'); ?>