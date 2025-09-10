<?php

class Disciplina
{
    public $id;
    public $nome;

    public function __construct($data)
    {
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? '';
    }

    /**
     * Find all disciplines.
     *
     * @return Disciplina[]
     */
    public static function findAll()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM discipline ORDER BY nome ASC');
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $discipline = [];
        foreach ($results as $data) {
            $discipline[] = new self($data);
        }
        return $discipline;
    }

    /**
     * Find a single discipline by its ID.
     *
     * @param int $id
     * @return Disciplina|null
     */
    public static function findById($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM discipline WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new self($data) : null;
    }

    /**
     * Save the discipline (insert or update).
     *
     * @return bool
     */
    public function save()
    {
        $database = new Database();
        $pdo = $database->getConnection();

        if ($this->id) {
            $stmt = $pdo->prepare('UPDATE discipline SET nome = :nome WHERE id = :id');
            $params = ['nome' => $this->nome, 'id' => $this->id];
        } else {
            $stmt = $pdo->prepare('INSERT INTO discipline (nome) VALUES (:nome)');
            $params = ['nome' => $this->nome];
        }

        $result = $stmt->execute($params);

        if ($result && !$this->id) {
            $this->id = $pdo->lastInsertId();
        }

        return $result;
    }

    /**
     * Delete a discipline by its ID.
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('DELETE FROM discipline WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
