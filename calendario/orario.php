<?php
require_once '../header.php';
require_once '../src/Database.php';
require_once '../src/Orario.php';
require_once '../src/Disciplina.php';

$database = Database::getInstance();
$db = $database->getConnection();

$orario = new Orario($db);
$orario->user_id = $_SESSION['id'];
$stmt_orari = $orario->read();

$disciplina = new Disciplina($db);
$stmt_discipline = $disciplina->read();

$giorni_settimana = [
    1 => 'Lunedì',
    2 => 'Martedì',
    3 => 'Mercoledì',
    4 => 'Giovedì',
    5 => 'Venerdì',
    6 => 'Sabato',
    7 => 'Domenica'
];

?>

<div class="container mt-4">
    <h1>Gestione Orario Lezioni</h1>

    <div class="card mb-4">
        <div class="card-header">
            Aggiungi Nuova Lezione Ricorrente
        </div>
        <div class="card-body">
            <form action="save_orario.php" method="post">
                <div class="row">
                    <div class="col-md-3">
                        <label for="disciplina_id" class="form-label">Disciplina</label>
                        <select id="disciplina_id" name="disciplina_id" class="form-select" required>
                            <?php while ($row = $stmt_discipline->fetch(PDO::FETCH_ASSOC)) : ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['nome']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="giorno_settimana" class="form-label">Giorno</label>
                        <select id="giorno_settimana" name="giorno_settimana" class="form-select" required>
                            <?php foreach ($giorni_settimana as $num => $nome) : ?>
                                <option value="<?php echo $num; ?>"><?php echo $nome; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="ora_inizio" class="form-label">Ora Inizio</label>
                        <input type="time" id="ora_inizio" name="ora_inizio" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label for="ora_fine" class="form-label">Ora Fine</label>
                        <input type="time" id="ora_fine" name="ora_fine" class="form-control" required>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label for="validita_inizio" class="form-label">Inizio Validità</label>
                        <input type="date" id="validita_inizio" name="validita_inizio" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label for="validita_fine" class="form-label">Fine Validità</label>
                        <input type="date" id="validita_fine" name="validita_fine" class="form-control" required>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary">Aggiungi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Orario Attuale
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Disciplina</th>
                        <th>Giorno</th>
                        <th>Ora Inizio</th>
                        <th>Ora Fine</th>
                        <th>Inizio Validità</th>
                        <th>Fine Validità</th>
                        <th>Azione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt_orari->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['disciplina_nome']); ?></td>
                            <td><?php echo $giorni_settimana[$row['giorno_settimana']]; ?></td>
                            <td><?php echo $row['ora_inizio']; ?></td>
                            <td><?php echo $row['ora_fine']; ?></td>
                            <td><?php echo $row['validita_inizio']; ?></td>
                            <td><?php echo $row['validita_fine']; ?></td>
                            <td>
                                <a href="save_orario.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Sei sicuro di voler eliminare questo orario?');">Elimina</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../footer.php'; ?>
