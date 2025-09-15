<?php
// Note: Authentication is handled by header.php
require_once '../header.php';
require_once '../src/Database.php';
require_once '../src/Documento.php';

$id = isset($_GET['id']) ? $_GET['id'] : die('ID non specificato.');

$db = Database::getInstance()->getConnection();
$documento = new Documento($db);
$documento->id = $id;
$documento->readOne();
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4>Modifica Documento</h4>
        </div>
        <div class="card-body">
            <form action="update.php" method="post">
                <input type="hidden" name="id" value="<?php echo $documento->id; ?>">
                <div class="mb-3">
                    <label for="topic" class="form-label">Argomento</label>
                    <input type="text" class="form-control" id="topic" name="topic" value="<?php echo htmlspecialchars($documento->topic); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Descrizione</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($documento->description); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salva Modifiche</button>
                <a href="admin.php" class="btn btn-secondary"><i class="fas fa-times"></i> Annulla</a>
            </form>
        </div>
    </div>
</div>

<?php
require_once '../footer.php';
?>
