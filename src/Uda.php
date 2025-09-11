<?php

class Uda
{
    private $conn;

    public $id;
    public $name;
    public $description;
    public $disciplina_id;
    public $anno_corso;
    public $disciplina_name;

    public function __construct($db, $data = [])
    {
        $this->conn = $db;
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->disciplina_id = $data['disciplina_id'] ?? null;
        $this->anno_corso = $data['anno_corso'] ?? null;
        $this->disciplina_name = $data['disciplina_name'] ?? null;
    }

    /**
     * Find all UDAs.
     *
     * @return Uda[]
     */
    public function findAll()
    {
        $query = '
            SELECT
                u.*,
                d.nome AS disciplina_name
            FROM
                udas u
            LEFT JOIN
                discipline d ON u.disciplina_id = d.id
            ORDER BY
                u.name ASC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $udaData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $udas = [];
        foreach ($udaData as $data) {
            $udas[] = new self($this->conn, $data);
        }
        return $udas;
    }

    /**
     * Find a single UDA by its ID.
     *
     * @param int $id
     * @return Uda|null
     */
    public function findById($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM udas WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new self($this->conn, $data);
        }
        return null;
    }

    /**
     * Save the UDA (insert or update).
     *
     * @return bool|string True on success, error message string on failure.
     */
    public function save()
    {
        if ($this->id) {
            // Update existing UDA
            $sql = 'UPDATE udas SET name = :name, description = :description, disciplina_id = :disciplina_id, anno_corso = :anno_corso WHERE id = :id';
            $params = [
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'disciplina_id' => $this->disciplina_id,
                'anno_corso' => $this->anno_corso,
            ];
        } else {
            // Insert new UDA
            $sql = 'INSERT INTO udas (name, description, disciplina_id, anno_corso) VALUES (:name, :description, :disciplina_id, :anno_corso)';
            $params = [
                'name' => $this->name,
                'description' => $this->description,
                'disciplina_id' => $this->disciplina_id,
                'anno_corso' => $this->anno_corso,
            ];
        }

        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            if (!$this->id) {
                $this->id = $this->conn->lastInsertId();
            }
            return true;
        } else {
            $errorInfo = $stmt->errorInfo();
            return "DB Error: " . ($errorInfo[2] ?? 'Unknown error');
        }
    }

    /**
     * Delete a UDA by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare('DELETE FROM udas WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
