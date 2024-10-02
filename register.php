<?php
include('header.php');
include('condb.php');

if (isset($_POST['register'])) {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (nom, email, mot_de_passe, droit, etat) VALUES (?, ?, ?, 'user', 'active')");
    $stmt->execute([$nom, $email, $password]);

    echo "User registered successfully!";
}
?>

<div class="content">
    <h2>Register</h2>
    <form method="post">
        <div class="form-group">
            <label for="nom">Name</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="register" class="btn btn-primary">Register</button>
    </form>
</div>

<?php include('footer.php'); ?>
