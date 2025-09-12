<?php

class Module
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
     * Find all Modules, optionally filtering by year.
     *
     * @param int|null $anno_corso
     * @return Module[]
     */
    public function findAll($anno_corso = null, $disciplina_id = null)
    {
        $params = [];
        $query = '
            SELECT
                m.*,
                d.nome AS disciplina_name
            FROM
                modules m
            LEFT JOIN
                discipline d ON m.disciplina_id = d.id';

        $where_clauses = [];
        if ($anno_corso) {
            $where_clauses[] = 'm.anno_corso = :anno_corso';
            $params[':anno_corso'] = $anno_corso;
        }
        if ($disciplina_id) {
            $where_clauses[] = 'm.disciplina_id = :disciplina_id';
            $params[':disciplina_id'] = $disciplina_id;
        }

        if (count($where_clauses) > 0) {
            $query .= ' WHERE ' . implode(' AND ', $where_clauses);
        }

        $query .= ' ORDER BY m.name ASC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        $moduleData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $modules = [];
        foreach ($moduleData as $data) {
            $modules[] = new self($this->conn, $data);
        }
        return $modules;
    }

    /**
     * Find a single Module by its ID.
     *
     * @param int $id
     * @return Module|null
     */
    public function findById($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM modules WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new self($this->conn, $data);
        }
        return null;
    }

    /**
     * Save the Module (insert or update).
     *
     * @return bool|string True on success, error message string on failure.
     */
    public function save()
    {
        // Handle empty strings for nullable integer columns
        if ($this->anno_corso === '') {
            $this->anno_corso = null;
        }
        if ($this->disciplina_id === '') {
            $this->disciplina_id = null;
        }

        if ($this->id) {
            // Update existing Module
            $sql = 'UPDATE modules SET name = :name, description = :description, disciplina_id = :disciplina_id, anno_corso = :anno_corso WHERE id = :id';
            $params = [
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'disciplina_id' => $this->disciplina_id,
                'anno_corso' => $this->anno_corso,
            ];
        } else {
            // Insert new Module
            $sql = 'INSERT INTO modules (name, description, disciplina_id, anno_corso) VALUES (:name, :description, :disciplina_id, :anno_corso)';
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
     * Delete a Module by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare('DELETE FROM modules WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
