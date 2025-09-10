<?php

class TipologiaCompetenza
{
    public $id;
    public $nome;

    public function __construct($data)
    {
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? '';
    }

    /**
     * Find all competency types.
     *
     * @return TipologiaCompetenza[]
     */
    public static function findAll()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM tipologie_competenze ORDER BY nome ASC');
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tipologie = [];
        foreach ($results as $data) {
            $tipologie[] = new self($data);
        }
        return $tipologie;
    }

    /**
     * Find a single competency type by its ID.
     *
     * @param int $id
     * @return TipologiaCompetenza|null
     */
    public static function findById($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM tipologie_competenze WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new self($data) : null;
    }

    /**
     * Save the competency type (insert or update).
     *
     * @return bool
     */
    public function save()
    {
        $database = new Database();
        $pdo = $database->getConnection();

        if ($this->id) {
            $stmt = $pdo->prepare('UPDATE tipologie_competenze SET nome = :nome WHERE id = :id');
            $params = ['nome' => $this->nome, 'id' => $this->id];
        } else {
            $stmt = $pdo->prepare('INSERT INTO tipologie_competenze (nome) VALUES (:nome)');
            $params = ['nome' => $this->nome];
        }

        $result = $stmt->execute($params);

        if ($result && !$this->id) {
            $this->id = $pdo->lastInsertId();
        }

        return $result;
    }

    /**
     * Delete a competency type by its ID.
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('DELETE FROM tipologie_competenze WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
