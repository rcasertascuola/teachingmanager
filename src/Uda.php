<?php

class Uda
{
    private $conn;

    public $id;
    public $name;
    public $description;

    public function __construct($db, $data = [])
    {
        $this->conn = $db;
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
    }

    /**
     * Find all UDAs.
     *
     * @return Uda[]
     */
    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM udas ORDER BY name ASC');
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
            $sql = 'UPDATE udas SET name = :name, description = :description WHERE id = :id';
            $params = [
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
            ];
        } else {
            // Insert new UDA
            $sql = 'INSERT INTO udas (name, description) VALUES (:name, :description)';
            $params = [
                'name' => $this->name,
                'description' => $this->description,
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
