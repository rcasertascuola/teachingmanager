<?php
require_once '../src/Database.php';
require_once '../src/Documento.php';

$db = Database::getInstance()->getConnection();
$documento = new Documento($db);
$stmt = $documento->readAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elenco Documenti</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4>Elenco Documenti</h4>
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
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

