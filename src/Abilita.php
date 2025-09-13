<?php

class Abilita
{
    private $conn;

    public $id;
    public $nome;
    public $descrizione;
    public $tipo;

    // Related data
    public $anni_corso;
    public $discipline;

    public function __construct($db, $data = [])
    {
        $this->conn = $db;
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? '';
        $this->descrizione = $data['descrizione'] ?? '';
        $this->tipo = $data['tipo'] ?? 'cognitiva'; // Default value

        // These will be loaded separately
        $this->anni_corso = $data['anni_corso'] ?? [];
        $this->discipline = $data['discipline'] ?? [];
    }

    /**
     * Find all skills.
     *
     * @return Abilita[]
     */
    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM abilita ORDER BY nome ASC');
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $abilita_list = [];
        $abilita_ids = [];
        foreach ($results as $data) {
            $abilita_list[] = new self($this->conn, $data);
            $abilita_ids[] = $data['id'];
        }

        if (empty($abilita_ids)) {
            return $abilita_list;
        }

        // Fetch all related anni_corso in a single query
        $ids_placeholder = implode(',', array_fill(0, count($abilita_ids), '?'));
        $stmt_anni = $this->conn->prepare("
            SELECT abilita_id, anno_corso
            FROM abilita_anni_corso
            WHERE abilita_id IN ({$ids_placeholder})
            ORDER BY anno_corso ASC
        ");
        $stmt_anni->execute($abilita_ids);
        $anni_map = [];
        while ($row = $stmt_anni->fetch(PDO::FETCH_ASSOC)) {
            $anni_map[$row['abilita_id']][] = $row['anno_corso'];
        }

        // Assign the anni_corso to each
        foreach ($abilita_list as $abilita) {
            if (isset($anni_map[$abilita->id])) {
                $abilita->anni_corso = $anni_map[$abilita->id];
            }
        }

        // Fetch all related disciplines in a single query
        $stmt_disc = $this->conn->prepare("
            SELECT ad.abilita_id, d.nome
            FROM abilita_discipline ad
            JOIN discipline d ON ad.disciplina_id = d.id
            WHERE ad.abilita_id IN ({$ids_placeholder})
            ORDER BY d.nome ASC
        ");
        $stmt_disc->execute($abilita_ids);
        $disc_map = [];
        while ($row = $stmt_disc->fetch(PDO::FETCH_ASSOC)) {
            $disc_map[$row['abilita_id']][] = $row['nome'];
        }

        // Assign the disciplines to each
        foreach ($abilita_list as $abilita) {
            if (isset($disc_map[$abilita->id])) {
                $abilita->discipline = $disc_map[$abilita->id];
            }
        }

        return $abilita_list;
    }

    /**
     * Find a single skill by its ID, including related data.
     *
     * @param int $id
     * @return Abilita|null
     */
    public function findById($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM abilita WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $abilita = new self($this->conn, $data);
            $abilita->loadRelatedData();
            return $abilita;
        }
        return null;
    }

    /**
     * Save the skill (insert or update) and its relationships.
     *
     * @return bool
     */
    public function save()
    {
        try {
            $this->conn->beginTransaction();

            if ($this->id) {
                $stmt = $this->conn->prepare('UPDATE abilita SET nome = :nome, descrizione = :descrizione, tipo = :tipo WHERE id = :id');
                $params = [
                    'nome' => $this->nome,
                    'descrizione' => $this->descrizione,
                    'tipo' => $this->tipo,
                    'id' => $this->id
                ];
            } else {
                $stmt = $this->conn->prepare('INSERT INTO abilita (nome, descrizione, tipo) VALUES (:nome, :descrizione, :tipo)');
                $params = [
                    'nome' => $this->nome,
                    'descrizione' => $this->descrizione,
                    'tipo' => $this->tipo
                ];
            }

            $stmt->execute($params);

            if (!$this->id) {
                $this->id = $this->conn->lastInsertId();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            // In a real app, log the error: error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Delete a skill by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare('DELETE FROM abilita WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Loads related data.
     */
    private function loadRelatedData()
    {
        // Load anni_corso
        $stmt_anni = $this->conn->prepare('SELECT anno_corso FROM abilita_anni_corso WHERE abilita_id = :id ORDER BY anno_corso ASC');
        $stmt_anni->execute(['id' => $this->id]);
        $this->anni_corso = $stmt_anni->fetchAll(PDO::FETCH_COLUMN, 0);

        // Load discipline
        $stmt_disc = $this->conn->prepare('
            SELECT d.nome
            FROM abilita_discipline ad
            JOIN discipline d ON ad.disciplina_id = d.id
            WHERE ad.abilita_id = :id
            ORDER BY d.nome ASC
        ');
        $stmt_disc->execute(['id' => $this->id]);
        $this->discipline = $stmt_disc->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function findByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->conn->prepare("SELECT * FROM abilita WHERE id IN ({$placeholders})");
        $stmt->execute($ids);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $items = [];
        foreach ($results as $data) {
            $items[] = new self($this->conn, $data);
        }
        return $items;
    }
}
