<?php
require_once '../src/Database.php';
require_once '../src/Disciplina.php';
include '../header.php';

// Teacher role check
if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

// Get the database connection
$db = Database::getInstance()->getConnection();
$disciplina_manager = new Disciplina($db);

$disciplina = null;
if (isset($_GET['id'])) {
    $disciplina = $disciplina_manager->findById($_GET['id']);
} else {
    $disciplina = new Disciplina($db);
}

$pageTitle = $disciplina ? 'Modifica Disciplina' : 'Crea Nuova Disciplina';
$formAction = 'save.php';
?>
    <div class="container mt-5">
        <h2><?php echo $pageTitle; ?></h2>
        <form action="<?php echo $formAction; ?>" method="post">
            <?php if ($disciplina && $disciplina->id): ?>
                <input type="hidden" name="id" value="<?php echo $disciplina->id; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($disciplina->nome ?? ''); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Salva</button>
            <a href="index.php" class="btn btn-secondary">Annulla</a>
        </form>
    </div>
<?php include '../footer.php'; ?>
