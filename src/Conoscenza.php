<?php

class Conoscenza
{
    private $conn;

    public $id;
    public $nome;
    public $descrizione;

    public function __construct($db, $data = [])
    {
        $this->conn = $db;
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? '';
        $this->descrizione = $data['descrizione'] ?? '';
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

}
