<?php

class Uda
{
    public $id;
    public $name;
    public $description;

    public function __construct($data)
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
    }

    /**
     * Find all UDAs.
     *
     * @return Uda[]
     */
    public static function findAll()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM udas ORDER BY name ASC');
        $stmt->execute();

        $udaData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $udas = [];
        foreach ($udaData as $data) {
            $udas[] = new self($data);
        }
        return $udas;
    }

    /**
     * Find a single UDA by its ID.
     *
     * @param int $id
     * @return Uda|null
     */
    public static function findById($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM udas WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new self($data);
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
        $database = new Database();
        $pdo = $database->getConnection();

        if (!$pdo) {
            return "Failed to connect to the database.";
        }

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

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            if (!$this->id) {
                $this->id = $pdo->lastInsertId();
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
    public static function delete($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('DELETE FROM udas WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
