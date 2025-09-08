<?php
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include_once 'src/Database.php';
    include_once 'src/User.php';

    $database = new Database();
    $db = $database->getConnection();

    $user = new User($db);

    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    $user->role = $_POST['role'];

    if ($user->register()) {
        $message = "<div class='alert alert-success'>Registrazione completata con successo. Puoi <a href='login.php'>accedere</a> ora.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Errore durante la registrazione. L'username potrebbe essere già in uso.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Registrazione</h3>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        <form action="register.php" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Ruolo</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="student">Alunno</option>
                                    <option value="teacher">Insegnante</option>
                                </select>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Registrati</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <small>Hai già un account? <a href="login.php">Accedi</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
