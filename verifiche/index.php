<?php
require_once '../src/Database.php';
require_once '../src/Verifica.php';
include '../header.php';

// Fetch all verifiche from the database
$verifiche = Verifica::findAll();

$is_teacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Gestione Verifiche</h1>
        <?php if ($is_teacher): ?>
            <a href="edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Aggiungi Nuova Verifica</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header">
            Elenco Verifiche
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Titolo</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Data Creazione</th>
                            <th scope="col">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($verifiche)): ?>
                            <tr>
                                <td colspan="4" class="text-center">Nessuna verifica trovata. <br><small>Nota: la funzionalità è in costruzione. Il modello dati non è ancora stato implementato.</small></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($verifiche as $verifica): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($verifica->titolo); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($verifica->tipo)); ?></td>
                                    <td><?php echo htmlspecialchars($verifica->created_at); ?></td>
                                    <td>
                                        <a href="view.php?id=<?php echo $verifica->id; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        <?php if ($is_teacher): ?>
                                        <a href="registro.php?id=<?php echo $verifica->id; ?>" class="btn btn-sm btn-success"><i class="fas fa-book"></i></a>
                                        <a href="edit.php?id=<?php echo $verifica->id; ?>" class="btn btn-sm btn-warning"><i class="fas fa-pencil-alt"></i></a>
                                        <a href="delete.php?id=<?php echo $verifica->id; ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                                        <?php endif; ?>
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
