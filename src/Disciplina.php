<?php

class Disciplina
{
    private $conn;

    public $id;
    public $nome;

    public function __construct($db, $data = [])
    {
        $this->conn = $db;
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? '';
    }

    /**
     * Find all disciplines.
     *
     * @return Disciplina[]
     */
    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM discipline ORDER BY nome ASC');
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $discipline = [];
        foreach ($results as $data) {
            $discipline[] = new self($this->conn, $data);
        }
        return $discipline;
    }

    /**
     * Find a single discipline by its ID.
     *
     * @param int $id
     * @return Disciplina|null
     */
    public function findById($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM discipline WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new self($this->conn, $data) : null;
    }

    /**
     * Save the discipline (insert or update).
     *
     * @return bool
     */
    public function save()
    {
        if ($this->id) {
            $stmt = $this->conn->prepare('UPDATE discipline SET nome = :nome WHERE id = :id');
            $params = ['nome' => $this->nome, 'id' => $this->id];
        } else {
            $stmt = $this->conn->prepare('INSERT INTO discipline (nome) VALUES (:nome)');
            $params = ['nome' => $this->nome];
        }

        $result = $stmt->execute($params);

        if ($result && !$this->id) {
            $this->id = $this->conn->lastInsertId();
        }

        return $result;
    }

    /**
     * Delete a discipline by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare('DELETE FROM discipline WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
