<?php
include('header.php');
include('condb.php');

// CSRF 
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['register'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Erreur CSRF, opération non autorisée.');
    }

    // XSS
    $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (!$email) {
        echo "Adresse e-mail invalide.";
    } elseif (strlen($password) < 8) {
        echo "Le mot de passe doit contenir au moins 8 caractères.";
    } else {
        // password hash
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (nom, email, mot_de_passe, droit, etat) VALUES (?, ?, ?, 'user', 'active')");
            $stmt->execute([$nom, $email, $hashed_password]);

            echo "Utilisateur enregistré avec succès !";
        } catch (PDOException $e) {
            echo "Erreur lors de l'enregistrement de l'utilisateur : " . $e->getMessage();
        }
    }
}
?>

<div class="content">
    <h2>Register</h2>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="register" class="btn btn-primary">Register</button>
    </form>
</div>

<?php include('footer.php'); ?>
