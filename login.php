<?php
include('header.php');
include('condb.php');

// CSRF
function GenerateCsrfToken(){
    return bin2hex(random_bytes(32));
}

$token = GenerateCsrfToken();
$_SESSION['csrf_token'] = $token;
StoreCsrfToken($pdo, $token);

function StoreCsrfToken($pdo, $token){
    $stmt = $pdo->prepare("INSERT INTO csrf_tokens (token) VALUES (?)");
    $stmt->execute([$token]);
}

function VerifyCsrfToken($pdo, $token){
    $stmt = $pdo->prepare("SELECT * FROM csrf_tokens WHERE token = ?");
    $stmt->execute([$token]);
    $csrfTokenRecord = $stmt->fetch();

    if($csrfTokenRecord){
        $stmt = $pdo->prepare("DELETE FROM csrf_tokens WHERE id = ?");
        $stmt->execute([$csrfTokenRecord['id']]);
        return true;
    } else {
        return false;
    }
}

// CSRF Token Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'];
    if(!VerifyCsrfToken($pdo, $token)){
        die("Erreur CSRF : Token CSRF invalide");
    }

    // XSS Protection
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (!$email) {
        echo "<p style='color:red;'>Adresse e-mail invalide !</p>";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['droit'];
            $_SESSION['user_name'] = $user['nom'];

            header('Location: index.php');
            exit();
        } else {
            echo "<p style='color:red;'>Identifiants de connexion incorrects !</p>";
        }
    }
}
?>
<style>
    .login-page {
        background-color: #343a40; 
        color: #fff; 
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-box {
        width: 360px;
    }
    .login-card-body {
        background-color: #1e1e1e;
        border-radius: 10px;
        padding: 20px;
    }
    .form-control {
        background-color: #2c2c2c; 
        border: 1px solid #555;
        color: #fff;
    }
    .form-control::placeholder {
        color: #bbb; 
    }
    .input-group-text {
        background-color: #2c2c2c;
        border: 1px solid #555;
    }
    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }
    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #004085;
    }
</style>

<div class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="#"><b>Task</b>Manager</a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Connectez-vous pour commencer votre session</p>

                <form action="" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">Login</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
