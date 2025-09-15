<?php
require_once '../header.php';
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4>Carica Nuovo Documento</h4>
        </div>
        <div class="card-body">
            <form action="save.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="file" class="form-label">Seleziona File</label>
                    <input type="file" class="form-control" id="file" name="file" required>
                </div>
                <div class="mb-3">
                    <label for="topic" class="form-label">Argomento</label>
                    <input type="text" class="form-control" id="topic" name="topic" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Descrizione</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Carica</button>
                <a href="index.php" class="btn btn-secondary">Annulla</a>
            </form>
        </div>
    </div>
</div>

<?php
require_once '../footer.php';
?>
