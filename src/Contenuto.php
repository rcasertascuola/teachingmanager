<?php

class Contenuto
{
    private $conn;

    public $id;
    public $nome;
    public $descrizione;

    // Related data
    public $conoscenze;
    public $abilita;

    public function __construct($db, $data = [])
    {
        $this->conn = $db;
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? '';
        $this->descrizione = $data['descrizione'] ?? '';

        // These will be loaded separately
        $this->conoscenze = $data['conoscenze'] ?? [];
        $this->abilita = $data['abilita'] ?? [];
    }

    /**
     * Find all contenuti.
     *
     * @return Contenuto[]
     */
    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM contenuti ORDER BY nome ASC');
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $contenuti_list = [];
        foreach ($results as $data) {
            $contenuti_list[] = new self($this->conn, $data);
        }
        return $contenuti_list;
    }

    /**
     * Find a single contenuto by its ID, including related data.
     *
     * @param int $id
     * @return Contenuto|null
     */
    public function findById($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM contenuti WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $contenuto = new self($this->conn, $data);
            $contenuto->loadRelatedData();
            return $contenuto;
        }
        return null;
    }

    /**
     * Save the contenuto (insert or update) and its relationships.
     *
     * @return bool
     */
    public function save()
    {
        try {
            $this->conn->beginTransaction();

            if ($this->id) {
                $stmt = $this->conn->prepare('UPDATE contenuti SET nome = :nome, descrizione = :descrizione WHERE id = :id');
                $params = [
                    'nome' => $this->nome,
                    'descrizione' => $this->descrizione,
                    'id' => $this->id
                ];
            } else {
                $stmt = $this->conn->prepare('INSERT INTO contenuti (nome, descrizione) VALUES (:nome, :descrizione)');
                $params = [
                    'nome' => $this->nome,
                    'descrizione' => $this->descrizione
                ];
            }

            $stmt->execute($params);

            if (!$this->id) {
                $this->id = $this->conn->lastInsertId();
            }

            // Sync relationships
            $this->syncRelatedData('contenuto_conoscenze', 'conoscenza_id', $this->conoscenze);
            $this->syncRelatedData('contenuto_abilita', 'abilita_id', $this->abilita);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            // In a real app, log the error: error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Delete a contenuto by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare('DELETE FROM contenuti WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Loads related data.
     */
    private function loadRelatedData()
    {
        // Load conoscenze
        $stmt = $this->conn->prepare('SELECT conoscenza_id FROM contenuto_conoscenze WHERE contenuto_id = :id');
        $stmt->execute(['id' => $this->id]);
        $this->conoscenze = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Load abilita
        $stmt = $this->conn->prepare('SELECT abilita_id FROM contenuto_abilita WHERE contenuto_id = :id');
        $stmt->execute(['id' => $this->id]);
        $this->abilita = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * A generic helper to sync many-to-many relationships.
     */
    private function syncRelatedData($tableName, $relatedIdColumn, $relatedIds)
    {
        $thisIdColumn = 'contenuto_id';

        $stmt = $this->conn->prepare("DELETE FROM {$tableName} WHERE {$thisIdColumn} = :id");
        $stmt->execute(['id' => $this->id]);

        if (!empty($relatedIds)) {
            $sql = "INSERT INTO {$tableName} ({$thisIdColumn}, {$relatedIdColumn}) VALUES ";
            $placeholders = [];
            $values = [];
            foreach ($relatedIds as $relatedId) {
                $relatedId = (int)$relatedId;
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
