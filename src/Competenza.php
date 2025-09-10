<?php

class Competenza
{
    public $id;
    public $nome;
    public $descrizione;
    public $tipologia_id;

    // Related data
    public $conoscenze;
    public $abilita;
    public $discipline;
    public $anni_corso;

    public function __construct($data)
    {
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? '';
        $this->descrizione = $data['descrizione'] ?? '';
        $this->tipologia_id = $data['tipologia_id'] ?? null;

        // These will be loaded separately
        $this->conoscenze = $data['conoscenze'] ?? [];
        $this->abilita = $data['abilita'] ?? [];
        $this->discipline = $data['discipline'] ?? [];
        $this->anni_corso = $data['anni_corso'] ?? [];
    }

    /**
     * Find all competencies.
     *
     * @return Competenza[]
     */
    public static function findAll()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM competenze ORDER BY nome ASC');
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $competenze = [];
        foreach ($results as $data) {
            $competenze[] = new self($data);
        }
        return $competenze;
    }

    /**
     * Find a single competency by its ID, including related data.
     *
     * @param int $id
     * @return Competenza|null
     */
    public static function findById($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM competenze WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $competenza = new self($data);
            $competenza->loadRelatedData($pdo);
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
        $database = new Database();
        $pdo = $database->getConnection();

        try {
            $pdo->beginTransaction();

            if ($this->id) {
                $stmt = $pdo->prepare('UPDATE competenze SET nome = :nome, descrizione = :descrizione, tipologia_id = :tipologia_id WHERE id = :id');
                $params = [
                    'nome' => $this->nome,
                    'descrizione' => $this->descrizione,
                    'tipologia_id' => $this->tipologia_id,
                    'id' => $this->id
                ];
            } else {
                $stmt = $pdo->prepare('INSERT INTO competenze (nome, descrizione, tipologia_id) VALUES (:nome, :descrizione, :tipologia_id)');
                $params = [
                    'nome' => $this->nome,
                    'descrizione' => $this->descrizione,
                    'tipologia_id' => $this->tipologia_id
                ];
            }

            $stmt->execute($params);

            if (!$this->id) {
                $this->id = $pdo->lastInsertId();
            }

            // Sync relationships
            $this->syncRelatedData($pdo, 'competenza_conoscenze', 'competenza_id', 'conoscenza_id', $this->conoscenze);
            $this->syncRelatedData($pdo, 'competenza_abilita', 'competenza_id', 'abilita_id', $this->abilita);
            $this->syncRelatedData($pdo, 'competenza_discipline', 'competenza_id', 'disciplina_id', $this->discipline);
            $this->syncRelatedData($pdo, 'competenza_anni_corso', 'competenza_id', 'anno_corso', $this->anni_corso);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Delete a competency by its ID.
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('DELETE FROM competenze WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Loads related data.
     */
    private function loadRelatedData($pdo)
    {
        $this->conoscenze = $this->getRelatedIds($pdo, 'competenza_conoscenze', 'competenza_id', 'conoscenza_id');
        $this->abilita = $this->getRelatedIds($pdo, 'competenza_abilita', 'competenza_id', 'abilita_id');
        $this->discipline = $this->getRelatedIds($pdo, 'competenza_discipline', 'competenza_id', 'disciplina_id');
        $this->anni_corso = $this->getRelatedIds($pdo, 'competenza_anni_corso', 'competenza_id', 'anno_corso');
    }

    /**
     * Fetches related IDs from a join table.
     */
    private function getRelatedIds($pdo, $tableName, $thisIdColumn, $relatedIdColumn)
    {
        $stmt = $pdo->prepare("SELECT {$relatedIdColumn} FROM {$tableName} WHERE {$thisIdColumn} = :id");
        $stmt->execute(['id' => $this->id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * A generic helper to sync many-to-many relationships.
     */
    private function syncRelatedData($pdo, $tableName, $thisIdColumn, $relatedIdColumn, $relatedIds)
    {
        $stmt = $pdo->prepare("DELETE FROM {$tableName} WHERE {$thisIdColumn} = :id");
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
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
        }
    }
}
