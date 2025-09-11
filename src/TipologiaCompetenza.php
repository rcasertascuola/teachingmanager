<?php

class TipologiaCompetenza
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
     * Find all competency types.
     *
     * @return TipologiaCompetenza[]
     */
    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM tipologie_competenze ORDER BY nome ASC');
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tipologie = [];
        foreach ($results as $data) {
            $tipologie[] = new self($this->conn, $data);
        }
        return $tipologie;
    }

    /**
     * Find a single competency type by its ID.
     *
     * @param int $id
     * @return TipologiaCompetenza|null
     */
    public function findById($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM tipologie_competenze WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new self($this->conn, $data) : null;
    }

    /**
     * Save the competency type (insert or update).
     *
     * @return bool
     */
    public function save()
    {
        if ($this->id) {
            $stmt = $this->conn->prepare('UPDATE tipologie_competenze SET nome = :nome WHERE id = :id');
            $params = ['nome' => $this->nome, 'id' => $this->id];
        } else {
            $stmt = $this->conn->prepare('INSERT INTO tipologie_competenze (nome) VALUES (:nome)');
            $params = ['nome' => $this->nome];
        }

        $result = $stmt->execute($params);

        if ($result && !$this->id) {
            $this->id = $this->conn->lastInsertId();
        }

        return $result;
    }

    /**
     * Delete a competency type by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare('DELETE FROM tipologie_competenze WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
