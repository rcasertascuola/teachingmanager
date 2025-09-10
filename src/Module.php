<?php

class Module
{
    public $id;
    public $uda_id;
    public $name;
    public $description;

    public function __construct($data)
    {
        $this->id = $data['id'] ?? null;
        $this->uda_id = $data['uda_id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
    }

    /**
     * Find all modules.
     *
     * @return Module[]
     */
    public static function findAll()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM modules ORDER BY name ASC');
        $stmt->execute();

        $moduleData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $modules = [];
        foreach ($moduleData as $data) {
            $modules[] = new self($data);
        }
        return $modules;
    }

    /**
     * Find a single module by its ID.
     *
     * @param int $id
     * @return Module|null
     */
    public static function findById($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM modules WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new self($data);
        }
        return null;
    }

    /**
     * Find all modules for a given UDA.
     *
     * @param int $udaId
     * @return Module[]
     */
    public static function findByUdaId($udaId)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM modules WHERE uda_id = :uda_id ORDER BY name ASC');
        $stmt->execute(['uda_id' => $udaId]);

        $moduleData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $modules = [];
        foreach ($moduleData as $data) {
            $modules[] = new self($data);
        }
        return $modules;
    }

    /**
     * Save the module (insert or update).
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
            // Update existing module
            $sql = 'UPDATE modules SET uda_id = :uda_id, name = :name, description = :description WHERE id = :id';
            $params = [
                'id' => $this->id,
                'uda_id' => $this->uda_id,
                'name' => $this->name,
                'description' => $this->description,
            ];
        } else {
            // Insert new module
            $sql = 'INSERT INTO modules (uda_id, name, description) VALUES (:uda_id, :name, :description)';
            $params = [
                'uda_id' => $this->uda_id,
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
     * Delete a module by its ID.
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('DELETE FROM modules WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
