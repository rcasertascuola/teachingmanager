<?php

require_once __DIR__ . '/AnniCorsoManager.php';

class Competenza
{
    private $conn;

    public $id;
    public $nome;
    public $descrizione;
    public $tipologia_id;

    // Related data
    public $conoscenze;
    public $abilita;
    public $anni_corso;
    public $discipline;

    public function __construct($db, $data = [])
    {
        $this->conn = $db;
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? '';
        $this->descrizione = $data['descrizione'] ?? '';
        $this->tipologia_id = $data['tipologia_id'] ?? null;

        // These will be loaded separately if not provided
        $this->conoscenze = $data['conoscenze'] ?? [];
        $this->abilita = $data['abilita'] ?? [];
        $this->anni_corso = $data['anni_corso'] ?? [];
        $this->discipline = $data['discipline'] ?? [];
    }

    /**
     * Find all competencies.
     *
     * @return Competenza[]
     */
    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM competenze ORDER BY nome ASC');
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $competenze = [];
        $competenza_ids = [];
        foreach ($results as $data) {
            $competenze[] = new self($this->conn, $data);
            $competenza_ids[] = $data['id'];
        }

        if (empty($competenza_ids)) {
            return $competenze;
        }

        // Fetch all related anni_corso in a single query
        $ids_placeholder = implode(',', array_fill(0, count($competenza_ids), '?'));
        $stmt_anni = $this->conn->prepare("
            SELECT competenza_id, anno_corso
            FROM competenza_anni_corso
            WHERE competenza_id IN ({$ids_placeholder})
            ORDER BY anno_corso ASC
        ");
        $stmt_anni->execute($competenza_ids);
        $anni_map = [];
        while ($row = $stmt_anni->fetch(PDO::FETCH_ASSOC)) {
            $anni_map[$row['competenza_id']][] = $row['anno_corso'];
        }

        // Assign the anni_corso to each
        foreach ($competenze as $competenza) {
            if (isset($anni_map[$competenza->id])) {
                $competenza->anni_corso = $anni_map[$competenza->id];
            }
        }

        // Fetch all related disciplines in a single query
        $stmt_disc = $this->conn->prepare("
            SELECT cd.competenza_id, d.nome
            FROM competenza_discipline cd
            JOIN discipline d ON cd.disciplina_id = d.id
            WHERE cd.competenza_id IN ({$ids_placeholder})
            ORDER BY d.nome ASC
        ");
        $stmt_disc->execute($competenza_ids);
        $disc_map = [];
        while ($row = $stmt_disc->fetch(PDO::FETCH_ASSOC)) {
            $disc_map[$row['competenza_id']][] = $row['nome'];
        }

        // Assign the disciplines to each
        foreach ($competenze as $competenza) {
            if (isset($disc_map[$competenza->id])) {
                $competenza->discipline = $disc_map[$competenza->id];
            }
        }

        return $competenze;
    }

    /**
     * Find a single competency by its ID, including related data.
     *
     * @param int $id
     * @return Competenza|null
     */
    public function findById($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM competenze WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $competenza = new self($this->conn, $data);
            $competenza->loadRelatedData();
            return $competenza;
        }
        return null;
    }

    /**
     * Save the competency (insert or update) and its relationships.
     *
     * @return bool
     */
    public function save()
    {
        try {
            $this->conn->beginTransaction();

            if ($this->id) {
                $stmt = $this->conn->prepare('UPDATE competenze SET nome = :nome, descrizione = :descrizione, tipologia_id = :tipologia_id WHERE id = :id');
                $params = [
                    'nome' => $this->nome,
                    'descrizione' => $this->descrizione,
                    'tipologia_id' => $this->tipologia_id,
                    'id' => $this->id
                ];
            } else {
                $stmt = $this->conn->prepare('INSERT INTO competenze (nome, descrizione, tipologia_id) VALUES (:nome, :descrizione, :tipologia_id)');
                $params = [
                    'nome' => $this->nome,
                    'descrizione' => $this->descrizione,
                    'tipologia_id' => $this->tipologia_id
                ];
            }

            $stmt->execute($params);

            if (!$this->id) {
                $this->id = $this->conn->lastInsertId();
            }

            // Sync relationships
            $this->syncRelatedData('competenza_conoscenze', 'conoscenza_id', $this->conoscenze);
            $this->syncRelatedData('competenza_abilita', 'abilita_id', $this->abilita);

            // After all relationships are synced, trigger the course year recalculation
            $anniCorsoManager = new AnniCorsoManager($this->conn);
            if (!$anniCorsoManager->updateAll()) {
                throw new Exception("Failed to update course year associations.");
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            // In a real app, log the error
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Delete a competency by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare('DELETE FROM competenze WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Loads related data.
     */
    private function loadRelatedData()
    {
        $this->conoscenze = $this->getRelatedIds('competenza_conoscenze', 'conoscenza_id');
        $this->abilita = $this->getRelatedIds('competenza_abilita', 'abilita_id');

        // Load anni_corso
        $stmt_anni = $this->conn->prepare('SELECT anno_corso FROM competenza_anni_corso WHERE competenza_id = :id ORDER BY anno_corso ASC');
        $stmt_anni->execute(['id' => $this->id]);
        $this->anni_corso = $stmt_anni->fetchAll(PDO::FETCH_COLUMN, 0);
      
        // Load discipline
        $stmt_disc = $this->conn->prepare('
            SELECT d.nome
            FROM competenza_discipline cd
            JOIN discipline d ON cd.disciplina_id = d.id
            WHERE cd.competenza_id = :id
            ORDER BY d.nome ASC
        ');
        $stmt_disc->execute(['id' => $this->id]);
        $this->discipline = $stmt_disc->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Fetches related IDs from a join table.
     */
    private function getRelatedIds($tableName, $relatedIdColumn)
    {
        $thisIdColumn = 'competenza_id';
        $stmt = $this->conn->prepare("SELECT {$relatedIdColumn} FROM {$tableName} WHERE {$thisIdColumn} = :id");
        $stmt->execute(['id' => $this->id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * A generic helper to sync many-to-many relationships.
     */
    private function syncRelatedData($tableName, $relatedIdColumn, $relatedIds)
    {
        $thisIdColumn = 'competenza_id';
        $stmt = $this->conn->prepare("DELETE FROM {$tableName} WHERE {$thisIdColumn} = :id");
        $stmt->execute(['id' => $this->id]);

        if (!empty($relatedIds)) {
            $sql = "INSERT INTO {$tableName} ({$thisIdColumn}, {$relatedIdColumn}) VALUES ";
            $placeholders = [];
            $values = [];
            foreach ($relatedIds as $relatedId) {
                $placeholders[] = '(?, ?)';
                $values[] = $this->id;
                $values[] = $relatedId;
            }
            $sql .= implode(', ', $placeholders);
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
        }
    }
}
