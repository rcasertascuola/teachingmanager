<?php

require_once __DIR__ . '/AnniCorsoManager.php';

class Module
{
    private $conn;

    public $id;
    public $name;
    public $description;
    public $disciplina_id;
    public $anno_corso;
    public $tempo_stimato;
    public $disciplina_name;

    // Related data
    public $conoscenze;
    public $abilita;
    public $competenze;

    public function __construct($db, $data = [])
    {
        $this->conn = $db;
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->disciplina_id = $data['disciplina_id'] ?? null;
        $this->anno_corso = $data['anno_corso'] ?? null;
        $this->tempo_stimato = $data['tempo_stimato'] ?? null;
        $this->disciplina_name = $data['disciplina_name'] ?? null;

        // These will be loaded separately if not provided
        $this->conoscenze = $data['conoscenze'] ?? [];
        $this->abilita = $data['abilita'] ?? [];
        $this->competenze = $data['competenze'] ?? [];
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
            $module = new self($this->conn, $data);
            $module->loadRelatedData();
            return $module;
        }
        return null;
    }

    /**
     * Save the Module (insert or update) and trigger the update of course years.
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
        if ($this->tempo_stimato === '') {
            $this->tempo_stimato = null;
        }

        try {
            $this->conn->beginTransaction();

            if ($this->id) {
                // Update existing Module
                $sql = 'UPDATE modules SET name = :name, description = :description, disciplina_id = :disciplina_id, anno_corso = :anno_corso, tempo_stimato = :tempo_stimato WHERE id = :id';
                $params = [
                    'id' => $this->id,
                    'name' => $this->name,
                    'description' => $this->description,
                    'disciplina_id' => $this->disciplina_id,
                    'anno_corso' => $this->anno_corso,
                    'tempo_stimato' => $this->tempo_stimato,
                ];
            } else {
                // Insert new Module
                $sql = 'INSERT INTO modules (name, description, disciplina_id, anno_corso, tempo_stimato) VALUES (:name, :description, :disciplina_id, :anno_corso, :tempo_stimato)';
                $params = [
                    'name' => $this->name,
                    'description' => $this->description,
                    'disciplina_id' => $this->disciplina_id,
                    'anno_corso' => $this->anno_corso,
                    'tempo_stimato' => $this->tempo_stimato,
                ];
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            if (!$this->id) {
                $this->id = $this->conn->lastInsertId();
            }

            // Sync relationships
            $this->syncRelatedData('module_conoscenze', 'conoscenza_id', $this->conoscenze);
            $this->syncRelatedData('module_abilita', 'abilita_id', $this->abilita);
            $this->syncRelatedData('module_competenze', 'competenza_id', $this->competenze);

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

    /**
     * Loads related data (conoscenze, abilita, competenze).
     */
    private function loadRelatedData()
    {
        $this->conoscenze = $this->getRelatedIds('module_conoscenze', 'conoscenza_id');
        $this->abilita = $this->getRelatedIds('module_abilita', 'abilita_id');
        $this->competenze = $this->getRelatedIds('module_competenze', 'competenza_id');
    }

    /**
     * Fetches related IDs from a join table.
     */
    private function getRelatedIds($tableName, $relatedIdColumn)
    {
        $thisIdColumn = 'module_id';
        $stmt = $this->conn->prepare("SELECT {$relatedIdColumn} FROM {$tableName} WHERE {$thisIdColumn} = :id");
        $stmt->execute(['id' => $this->id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * A generic helper to sync many-to-many relationships.
     */
    private function syncRelatedData($tableName, $relatedIdColumn, $relatedIds)
    {
        $thisIdColumn = 'module_id';
        $stmt = $this->conn->prepare("DELETE FROM {$tableName} WHERE {$thisIdColumn} = :id");
        $stmt->execute(['id' => $this->id]);

        if (!empty($relatedIds)) {
            $sql = "INSERT INTO {$tableName} ({$thisIdColumn}, {$relatedIdColumn}) VALUES ";
            $placeholders = [];
            $values = [];
            foreach ($relatedIds as $relatedId) {
                $placeholders[] = '(?, ?)';
                $values[] = $this->id;
                $values[] = $relatedId;
            }
            $sql .= implode(', ', $placeholders);
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($values);
        }
    }

    public function getConoscenze()
    {
        if (empty($this->conoscenze) && $this->id) {
            $this->loadRelatedData();
        }
        return $this->conoscenze;
    }

    public function getAbilita()
    {
        if (empty($this->abilita) && $this->id) {
            $this->loadRelatedData();
        }
        return $this->abilita;
    }

    public function getCompetenze()
    {
        if (empty($this->competenze) && $this->id) {
            $this->loadRelatedData();
        }
        return $this->competenze;
    }
}
