<?php
require_once '../src/Database.php';
require_once '../src/Conoscenza.php';
include '../header.php';

// Teacher role check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Get the database connection
$db = Database::getInstance()->getConnection();

$conoscenza_manager = new Conoscenza($db);
$conoscenza = null;
if (isset($_GET['id'])) {
    $conoscenza = $conoscenza_manager->findById($_GET['id']);
} else {
    $conoscenza = new Conoscenza($db);
}

$pageTitle = $conoscenza ? 'Modifica Conoscenza' : 'Crea Nuova Conoscenza';
$formAction = 'save.php';
?>
    <div class="container mt-5">
        <h2><?php echo $pageTitle; ?></h2>
        <form action="<?php echo $formAction; ?>" method="post">
            <?php if ($conoscenza && $conoscenza->id): ?>
                <input type="hidden" name="id" value="<?php echo $conoscenza->id; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($conoscenza->nome ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="descrizione">Descrizione</label>
                <textarea class="form-control" id="descrizione" name="descrizione" rows="3"><?php echo htmlspecialchars($conoscenza->descrizione ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Salva</button>
            <a href="index.php" class="btn btn-secondary">Annulla</a>
        </form>
    </div>
<?php include '../footer.php'; ?>
