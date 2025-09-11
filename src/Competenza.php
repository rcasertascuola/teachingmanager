<?php

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
        foreach ($results as $data) {
            $competenze[] = new self($this->conn, $data);
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
