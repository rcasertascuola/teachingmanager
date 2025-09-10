<?php
require_once '../src/Database.php';
require_once '../src/Uda.php';
include '../header.php';

// Redirect to login if not logged in or not a teacher
if ($_SESSION["role"] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}


$udas = Uda::findAll();
?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">Gestione UDA</h1>
            <a href="edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Aggiungi Nuova UDA</a>
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
                                            <a href="edit.php?id=<?php echo $uda->id; ?>" class="btn btn-sm btn-warning"><i class="fas fa-pencil-alt"></i></a>
                                            <a href="delete.php?id=<?php echo $uda->id; ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
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

<?php include '../footer.php'; ?>
