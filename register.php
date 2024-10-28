<?php
include('header.php');
include('condb.php');

// CSRF Token Generation and Storage
function GenerateCsrfToken() {
    return bin2hex(random_bytes(32));
}

$token = GenerateCsrfToken();
$_SESSION['csrf_token'] = $token;
StoreCsrfToken($pdo, $token);

function StoreCsrfToken($pdo, $token) {
    $stmt = $pdo->prepare("INSERT INTO csrf_tokens (token) VALUES (?)");
    $stmt->execute([$token]);
}

function VerifyCsrfToken($pdo, $token) {
    $stmt = $pdo->prepare("SELECT * FROM csrf_tokens WHERE token = ?");
    $stmt->execute([$token]);
    $csrfTokenRecord = $stmt->fetch();

    if ($csrfTokenRecord) {
        $stmt = $pdo->prepare("DELETE FROM csrf_tokens WHERE id = ?");
        $stmt->execute([$csrfTokenRecord['id']]);
        return true;
    } else {
        return false;
    }
}

// Handle Registration Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $token = $_POST['csrf_token'];
    if (!VerifyCsrfToken($pdo, $token)) {
        die("Erreur CSRF : Token CSRF invalide");
    }

    // Input Validation (XSS Protection)
    $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (!$email) {
        echo "Adresse e-mail invalide.";
    } elseif (strlen($password) < 8) {
        echo "Le mot de passe doit contenir au moins 8 caractères.";
    } else {
        // Password Hashing
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
