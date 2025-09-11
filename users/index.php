<?php
require_once '../src/Database.php';
require_once '../src/User.php';
include '../header.php';

// Check if user is a teacher
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Gestione Utenti</h1>
        <a href="edit.php" class="btn btn-primary"><i class="fas fa-plus"></i></a>
    </div>

    <div class="card">
        <div class="card-header">
            Elenco Utenti
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped"
                       data-dynamic-table
                       data-table-name="users"
                       data-columns='["username", "role", "status", "classe", "corso", "anno_scolastico", "actions"]'>
                    <thead>
                        <tr>
                            <th scope="col">Username</th>
                            <th scope="col">Ruolo</th>
                            <th scope="col">Stato</th>
                            <th scope="col">Classe</th>
                            <th scope="col">Corso</th>
                            <th scope="col">Anno Scolastico</th>
                            <th scope="col">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic content will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
