<?php

class Conoscenza
{
    private $conn;

    public $id;
    public $nome;
    public $descrizione;

    // Related data
    public $discipline;
    public $anni_corso;

    public function __construct($db, $data = [])
    {
        $this->conn = $db;
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? '';
        $this->descrizione = $data['descrizione'] ?? '';

        // These will be loaded separately
        $this->discipline = $data['discipline'] ?? [];
        $this->anni_corso = $data['anni_corso'] ?? [];
    }

    /**
     * Find all knowledge entries.
     *
     * @return Conoscenza[]
     */
    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM conoscenze ORDER BY nome ASC');
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $conoscenze = [];
        foreach ($results as $data) {
            $conoscenze[] = new self($this->conn, $data);
        }
        return $conoscenze;
    }

    /**
     * Find a single knowledge entry by its ID, including related data.
     *
     * @param int $id
     * @return Conoscenza|null
     */
    public function findById($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM conoscenze WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $conoscenza = new self($this->conn, $data);
            $conoscenza->loadRelatedData();
            return $conoscenza;
        }
        return null;
    }

    /**
     * Save the knowledge entry (insert or update) and its relationships.
     *
     * @return bool
     */
    public function save()
    {
        try {
            $this->conn->beginTransaction();

            if ($this->id) {
                $stmt = $this->conn->prepare('UPDATE conoscenze SET nome = :nome, descrizione = :descrizione WHERE id = :id');
                $params = ['nome' => $this->nome, 'descrizione' => $this->descrizione, 'id' => $this->id];
            } else {
                $stmt = $this->conn->prepare('INSERT INTO conoscenze (nome, descrizione) VALUES (:nome, :descrizione)');
                $params = ['nome' => $this->nome, 'descrizione' => $this->descrizione];
            }

            $stmt->execute($params);

            if (!$this->id) {
                $this->id = $this->conn->lastInsertId();
            }

            // Sync disciplines
            $this->syncRelatedData('conoscenza_discipline', 'disciplina_id', $this->discipline);

            // Sync school years
            $this->syncRelatedData('conoscenza_anni_corso', 'anno_corso', $this->anni_corso);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            // In a real app, you'd log the error message ($e->getMessage())
            return false;
        }
    }

    /**
     * Delete a knowledge entry by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        // Transactions are good here too, as related data is deleted by CASCADE
        $stmt = $this->conn->prepare('DELETE FROM conoscenze WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Loads related disciplines and school years.
     */
    private function loadRelatedData()
    {
        // Load disciplines
        $stmt = $this->conn->prepare('SELECT disciplina_id FROM conoscenza_discipline WHERE conoscenza_id = :id');
        $stmt->execute(['id' => $this->id]);
        $this->discipline = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Load school years
        $stmt = $this->conn->prepare('SELECT anno_corso FROM conoscenza_anni_corso WHERE conoscenza_id = :id');
        $stmt->execute(['id' => $this->id]);
        $this->anni_corso = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * A generic helper to sync many-to-many relationships.
     */
    private function syncRelatedData($tableName, $relatedIdColumn, $relatedIds)
    {
        $thisIdColumn = 'conoscenza_id';
        // Delete existing relationships
        $stmt = $this->conn->prepare("DELETE FROM {$tableName} WHERE {$thisIdColumn} = :id");
        $stmt->execute(['id' => $this->id]);

        // Insert new relationships
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
