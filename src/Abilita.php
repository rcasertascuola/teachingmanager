<?php

class Abilita
{
    public $id;
    public $nome;
    public $descrizione;
    public $tipo;

    // Related data
    public $conoscenze;
    public $discipline;
    public $anni_corso;

    public function __construct($data)
    {
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? '';
        $this->descrizione = $data['descrizione'] ?? '';
        $this->tipo = $data['tipo'] ?? 'cognitiva'; // Default value

        // These will be loaded separately
        $this->conoscenze = $data['conoscenze'] ?? [];
        $this->discipline = $data['discipline'] ?? [];
        $this->anni_corso = $data['anni_corso'] ?? [];
    }

    /**
     * Find all skills.
     *
     * @return Abilita[]
     */
    public static function findAll()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM abilita ORDER BY nome ASC');
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $abilita = [];
        foreach ($results as $data) {
            $abilita[] = new self($data);
        }
        return $abilita;
    }

    /**
     * Find a single skill by its ID, including related data.
     *
     * @param int $id
     * @return Abilita|null
     */
    public static function findById($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM abilita WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $abilita = new self($data);
            $abilita->loadRelatedData($pdo);
            return $abilita;
        }
        return null;
    }

    /**
     * Save the skill (insert or update) and its relationships.
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
                $stmt = $pdo->prepare('UPDATE abilita SET nome = :nome, descrizione = :descrizione, tipo = :tipo WHERE id = :id');
                $params = [
                    'nome' => $this->nome,
                    'descrizione' => $this->descrizione,
                    'tipo' => $this->tipo,
                    'id' => $this->id
                ];
            } else {
                $stmt = $pdo->prepare('INSERT INTO abilita (nome, descrizione, tipo) VALUES (:nome, :descrizione, :tipo)');
                $params = [
                    'nome' => $this->nome,
                    'descrizione' => $this->descrizione,
                    'tipo' => $this->tipo
                ];
            }

            $stmt->execute($params);

            if (!$this->id) {
                $this->id = $pdo->lastInsertId();
            }

            // Sync relationships
            $this->syncRelatedData($pdo, 'abilita_conoscenze', 'abilita_id', 'conoscenza_id', $this->conoscenze);
            $this->syncRelatedData($pdo, 'abilita_discipline', 'abilita_id', 'disciplina_id', $this->discipline);
            $this->syncRelatedData($pdo, 'abilita_anni_corso', 'abilita_id', 'anno_corso', $this->anni_corso);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Delete a skill by its ID.
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('DELETE FROM abilita WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Loads related data.
     */
    private function loadRelatedData($pdo)
    {
        // Load conoscenze
        $stmt = $pdo->prepare('SELECT conoscenza_id FROM abilita_conoscenze WHERE abilita_id = :id');
        $stmt->execute(['id' => $this->id]);
        $this->conoscenze = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Load disciplines
        $stmt = $pdo->prepare('SELECT disciplina_id FROM abilita_discipline WHERE abilita_id = :id');
        $stmt->execute(['id' => $this->id]);
        $this->discipline = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Load school years
        $stmt = $pdo->prepare('SELECT anno_corso FROM abilita_anni_corso WHERE abilita_id = :id');
        $stmt->execute(['id' => $this->id]);
        $this->anni_corso = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
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
