<?php
require_once '../header.php';
require_once '../src/Database.php';
require_once '../src/Documento.php';

$db = Database::getInstance()->getConnection();
$documento = new Documento($db);
$stmt = $documento->readAll();
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4>Elenco Documenti</h4>
            <a href="upload.php" class="btn btn-primary float-end">Carica Nuovo Documento</a>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome File</th>
                        <th>Tipo</th>
                        <th>Dimensione</th>
                        <th>Argomento</th>
                        <th>Descrizione</th>
                        <th>Data Caricamento</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['filename']); ?></td>
                            <td><?php echo htmlspecialchars($row['file_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['size']); ?> bytes</td>
                            <td><?php echo htmlspecialchars($row['topic']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['upload_date']); ?></td>
                            <td>
                                <a href="download.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Download</a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Elimina</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once '../footer.php';
?>
