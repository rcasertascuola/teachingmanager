<?php
require_once '../src/Database.php';
require_once '../src/TipologiaCompetenza.php';
include '../header.php';

if ($_SESSION['role'] !== 'teacher') {
    echo "<div class='alert alert-danger'>Accesso negato.</div>";
    include '../footer.php';
    exit;
}

$tipologia = null;
if (isset($_GET['id'])) {
    $tipologia = TipologiaCompetenza::findById($_GET['id']);
}

$pageTitle = $tipologia ? 'Modifica Tipologia' : 'Crea Nuova Tipologia';
$formAction = 'save.php';
?>
    <div class="container mt-5">
        <h2><?php echo $pageTitle; ?></h2>
        <form action="<?php echo $formAction; ?>" method="post">
            <?php if ($tipologia && $tipologia->id): ?>
                <input type="hidden" name="id" value="<?php echo $tipologia->id; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($tipologia->nome ?? ''); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Salva</button>
            <a href="index.php" class="btn btn-secondary">Annulla</a>
        </form>
    </div>
<?php include '../footer.php'; ?>
