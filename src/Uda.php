<?php

require_once __DIR__ . '/AnniCorsoManager.php';

class Uda
{
    private $conn;

    public $id;
    public $module_id;
    public $name;
    public $description;

    public function __construct($db, $data = [])
    {
        $this->conn = $db;
        $this->id = $data['id'] ?? null;
        $this->module_id = $data['module_id'] ?? null;
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
     * Find all UDAs for a given Module.
     *
     * @param int $moduleId
     * @return Uda[]
     */
    public function findByModuleId($moduleId)
    {
        $stmt = $this->conn->prepare('SELECT * FROM udas WHERE module_id = :module_id ORDER BY name ASC');
        $stmt->execute(['module_id' => $moduleId]);

        $udaData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $udas = [];
        foreach ($udaData as $data) {
            $udas[] = new self($this->conn, $data);
        }
        return $udas;
    }

    /**
     * Save the UDA (insert or update) and trigger the update of course years.
     *
     * @return bool|string True on success, error message string on failure.
     */
    public function save()
    {
        try {
            $this->conn->beginTransaction();

            if ($this->id) {
                // Update existing UDA
                $sql = 'UPDATE udas SET module_id = :module_id, name = :name, description = :description WHERE id = :id';
                $params = [
                    'id' => $this->id,
                    'module_id' => $this->module_id,
                    'name' => $this->name,
                    'description' => $this->description,
                ];
            } else {
                // Insert new UDA
                $sql = 'INSERT INTO udas (module_id, name, description) VALUES (:module_id, :name, :description)';
                $params = [
                    'module_id' => $this->module_id,
                    'name' => $this->name,
                    'description' => $this->description,
                ];
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            if (!$this->id) {
                $this->id = $this->conn->lastInsertId();
            }

            // After saving, trigger the course year recalculation
            $anniCorsoManager = new AnniCorsoManager($this->conn);
            if (!$anniCorsoManager->updateAll()) {
                throw new Exception("Failed to update course year associations.");
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return "DB Error: " . $e->getMessage();
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
