<?php

class Conoscenza
{
    private $conn;

    public $id;
    public $nome;
    public $descrizione;

    public $anni_corso;

    public function __construct($db, $data = [])
    {
        $this->conn = $db;
        $this->id = $data['id'] ?? null;
        $this->nome = $data['nome'] ?? '';
        $this->descrizione = $data['descrizione'] ?? '';

        $this->anni_corso = $data['anni_corso'] ?? [];
    }

    /**
     * Find all knowledge entries.
     *
     * @return Conoscenza[]
     */
    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM conoscenze ORDER BY nome ASC');
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $conoscenze = [];
        $conoscenza_ids = [];
        foreach ($results as $data) {
            $conoscenze[] = new self($this->conn, $data);
            $conoscenza_ids[] = $data['id'];
        }

        if (empty($conoscenza_ids)) {
            return $conoscenze;
        }

        // Fetch all related anni_corso in a single query
        $ids_placeholder = implode(',', array_fill(0, count($conoscenza_ids), '?'));
        $stmt_anni = $this->conn->prepare("
            SELECT conoscenza_id, anno_corso
            FROM conoscenza_anni_corso
            WHERE conoscenza_id IN ({$ids_placeholder})
            ORDER BY anno_corso ASC
        ");
        $stmt_anni->execute($conoscenza_ids);
        $anni_map = [];
        while ($row = $stmt_anni->fetch(PDO::FETCH_ASSOC)) {
            $anni_map[$row['conoscenza_id']][] = $row['anno_corso'];
        }

        // Assign the anni_corso to each
        foreach ($conoscenze as $conoscenza) {
            if (isset($anni_map[$conoscenza->id])) {
                $conoscenza->anni_corso = $anni_map[$conoscenza->id];
            }
        }

        return $conoscenze;
    }

    /**
     * Find a single knowledge entry by its ID, including related data.
     *
     * @param int $id
     * @return Conoscenza|null
     */
    public function findById($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM conoscenze WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $conoscenza = new self($this->conn, $data);
            $conoscenza->loadAnniCorso();
            return $conoscenza;
        }
        return null;
    }

    /**
     * Loads the associated course years for this knowledge entry.
     */
    public function loadAnniCorso()
    {
        $stmt = $this->conn->prepare('SELECT anno_corso FROM conoscenza_anni_corso WHERE conoscenza_id = :id ORDER BY anno_corso ASC');
        $stmt->execute(['id' => $this->id]);
        $this->anni_corso = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Save the knowledge entry (insert or update) and its relationships.
     *
     * @return bool
     */
    public function save()
    {
        try {
            $this->conn->beginTransaction();

            if ($this->id) {
                $stmt = $this->conn->prepare('UPDATE conoscenze SET nome = :nome, descrizione = :descrizione WHERE id = :id');
                $params = ['nome' => $this->nome, 'descrizione' => $this->descrizione, 'id' => $this->id];
            } else {
                $stmt = $this->conn->prepare('INSERT INTO conoscenze (nome, descrizione) VALUES (:nome, :descrizione)');
                $params = ['nome' => $this->nome, 'descrizione' => $this->descrizione];
            }

            $stmt->execute($params);

            if (!$this->id) {
                $this->id = $this->conn->lastInsertId();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            // In a real app, you'd log the error message ($e->getMessage())
            return false;
        }
    }

    /**
     * Delete a knowledge entry by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        // Transactions are good here too, as related data is deleted by CASCADE
        $stmt = $this->conn->prepare('DELETE FROM conoscenze WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

}
