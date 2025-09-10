<?php
session_start();
// Redirect to login if not logged in or not a teacher
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'teacher') {
    header("location: ../login.php");
    exit;
}

require_once '../src/Database.php';
require_once '../src/Uda.php';

$udas = Uda::findAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestisci UDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">Gestionale Studio</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">Gestione UDA</h1>
            <a href="edit.php" class="btn btn-primary">Aggiungi Nuova UDA</a>
        </div>

        <div class="card">
            <div class="card-header">
                Elenco UDA
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Nome</th>
                                <th scope="col">Descrizione</th>
                                <th scope="col">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($udas)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">Nessuna UDA trovata.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($udas as $uda): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($uda->name); ?></td>
                                        <td><?php echo htmlspecialchars($uda->description); ?></td>
                                        <td>
                                            <a href="edit.php?id=<?php echo $uda->id; ?>" class="btn btn-sm btn-warning">Modifica</a>
                                            <a href="delete.php?id=<?php echo $uda->id; ?>" class="btn btn-sm btn-danger">Cancella</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
