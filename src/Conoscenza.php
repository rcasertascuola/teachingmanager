<?php

class Conoscenza
{
    public $id;
    public $nome;
    public $descrizione;

    // Related data
    public $discipline;
    public $anni_corso;

    public function __construct($data)
    {
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
    public static function findAll()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM conoscenze ORDER BY nome ASC');
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $conoscenze = [];
        foreach ($results as $data) {
            $conoscenze[] = new self($data);
        }
        return $conoscenze;
    }

    /**
     * Find a single knowledge entry by its ID, including related data.
     *
     * @param int $id
     * @return Conoscenza|null
     */
    public static function findById($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM conoscenze WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $conoscenza = new self($data);
            $conoscenza->loadRelatedData($pdo);
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
        $database = new Database();
        $pdo = $database->getConnection();

        try {
            $pdo->beginTransaction();

            if ($this->id) {
                $stmt = $pdo->prepare('UPDATE conoscenze SET nome = :nome, descrizione = :descrizione WHERE id = :id');
                $params = ['nome' => $this->nome, 'descrizione' => $this->descrizione, 'id' => $this->id];
            } else {
                $stmt = $pdo->prepare('INSERT INTO conoscenze (nome, descrizione) VALUES (:nome, :descrizione)');
                $params = ['nome' => $this->nome, 'descrizione' => $this->descrizione];
            }

            $stmt->execute($params);

            if (!$this->id) {
                $this->id = $pdo->lastInsertId();
            }

            // Sync disciplines
            $this->syncRelatedData($pdo, 'conoscenza_discipline', 'conoscenza_id', 'disciplina_id', $this->discipline);

            // Sync school years
            $this->syncRelatedData($pdo, 'conoscenza_anni_corso', 'conoscenza_id', 'anno_corso', $this->anni_corso);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
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
    public static function delete($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        // Transactions are good here too, as related data is deleted by CASCADE
        $stmt = $pdo->prepare('DELETE FROM conoscenze WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Loads related disciplines and school years.
     */
    private function loadRelatedData($pdo)
    {
        // Load disciplines
        $stmt = $pdo->prepare('SELECT disciplina_id FROM conoscenza_discipline WHERE conoscenza_id = :id');
        $stmt->execute(['id' => $this->id]);
        $this->discipline = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Load school years
        $stmt = $pdo->prepare('SELECT anno_corso FROM conoscenza_anni_corso WHERE conoscenza_id = :id');
        $stmt->execute(['id' => $this->id]);
        $this->anni_corso = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * A generic helper to sync many-to-many relationships.
     */
    private function syncRelatedData($pdo, $tableName, $thisIdColumn, $relatedIdColumn, $relatedIds)
    {
        // Delete existing relationships
        $stmt = $pdo->prepare("DELETE FROM {$tableName} WHERE {$thisIdColumn} = :id");
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
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
        }
    }
}
