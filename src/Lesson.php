<?php

require_once __DIR__ . '/AnniCorsoManager.php';

class Lesson
{
    private $conn;

    public $id;
    public $title;
    public $content;
    public $tags;
    public $uda_id;
    public $previous_lesson_id;
    public $created_at;
    public $updated_at;

    // Related data
    public $conoscenze;
    public $abilita;
    public $disciplina_nome;

    public function __construct($db, $data = [])
    {
        $this->conn = $db;
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? '';
        $this->content = $data['content'] ?? '';
        $this->tags = $data['tags'] ?? '';
        $this->uda_id = $data['uda_id'] ?? null;
        $this->previous_lesson_id = $data['previous_lesson_id'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;

        // For relationships
        $this->conoscenze = $data['conoscenze'] ?? [];
        $this->abilita = $data['abilita'] ?? [];

        // For inherited data
        $this->disciplina_nome = $data['disciplina_nome'] ?? null;
    }

    /**
     * Find all lessons with pagination.
     *
     * @param int $limit
     * @param int $offset
     * @return Lesson[]
     */
    public function findAll($limit = 10, $offset = 0)
    {
        $sql = "
            SELECT
                l.*,
                d.nome AS disciplina_nome
            FROM
                lessons l
            LEFT JOIN
                udas u ON l.uda_id = u.id
            LEFT JOIN
                modules m ON u.module_id = m.id
            LEFT JOIN
                discipline d ON m.disciplina_id = d.id
            ORDER BY
                l.updated_at DESC
        ";

        if ($limit !== null) {
            $sql .= ' LIMIT :limit OFFSET :offset';
        }

        $stmt = $this->conn->prepare($sql);

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();

        $lessonsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lessons = [];
        foreach ($lessonsData as $data) {
            $lessons[] = new self($this->conn, $data);
        }
        return $lessons;
    }

    /**
     * Find all lessons a specific student has interacted with.
     *
     * @param int $studentId
     * @return Lesson[]
     */
    public function findForStudent($studentId)
    {
        $stmt = $this->conn->prepare('
            SELECT l.* FROM lessons l
            JOIN (SELECT DISTINCT lesson_id FROM student_lesson_data WHERE user_id = :user_id) sld
            ON l.id = sld.lesson_id
            ORDER BY l.updated_at DESC
        ');
        $stmt->execute(['user_id' => $studentId]);

        $lessonsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lessons = [];
        foreach ($lessonsData as $data) {
            $lessons[] = new self($this->conn, $data);
        }
        return $lessons;
    }

    /**
     * Count all lessons.
     * @return int
     */
    public function countAll()
    {
        return (int) $this->conn->query('SELECT COUNT(id) FROM lessons')->fetchColumn();
    }

    /**
     * Find a single lesson by its ID.
     *
     * @param int $id
     * @return Lesson|null
     */
    public function findById($id)
    {
        $sql = "
            SELECT
                l.*,
                d.nome AS disciplina_nome
            FROM
                lessons l
            LEFT JOIN
                udas u ON l.uda_id = u.id
            LEFT JOIN
                modules m ON u.module_id = m.id
            LEFT JOIN
                discipline d ON m.disciplina_id = d.id
            WHERE
                l.id = :id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $lesson = new self($this->conn, $data);
            $lesson->loadRelatedData();
            return $lesson;
        }
        return null;
    }

    /**
     * Find a single lesson by its exact title.
     *
     * @param string $title
     * @return Lesson|null
     */
    public function findByTitle($title)
    {
        $stmt = $this->conn->prepare('SELECT * FROM lessons WHERE title = :title');
        $stmt->execute(['title' => $title]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new self($this->conn, $data);
        }
        return null;
    }

    /**
     * Save the lesson (insert or update).
     *
     * @return bool|string True on success, error message string on failure.
     */
    public function save()
    {
        // Handle empty strings for nullable integer columns
        if ($this->uda_id === '') {
            $this->uda_id = null;
        }
        if ($this->previous_lesson_id === '') {
            $this->previous_lesson_id = null;
        }

        try {
            $this->conn->beginTransaction();

            if ($this->id) {
                $stmt = $this->conn->prepare('UPDATE lessons SET title = :title, content = :content, tags = :tags, uda_id = :uda_id, previous_lesson_id = :previous_lesson_id WHERE id = :id');
                $params = [
                    'id' => $this->id,
                    'title' => $this->title,
                    'content' => $this->content,
                    'tags' => $this->tags,
                    'uda_id' => $this->uda_id,
                    'previous_lesson_id' => $this->previous_lesson_id,
                ];
            } else {
                $stmt = $this->conn->prepare('INSERT INTO lessons (title, content, tags, uda_id, previous_lesson_id) VALUES (:title, :content, :tags, :uda_id, :previous_lesson_id)');
                $params = [
                    'title' => $this->title,
                    'content' => $this->content,
                    'tags' => $this->tags,
                    'uda_id' => $this->uda_id,
                    'previous_lesson_id' => $this->previous_lesson_id,
                ];
            }

            $stmt->execute($params);

            if (!$this->id) {
                $this->id = $this->conn->lastInsertId();
            }

            // Sync relationships
            $this->syncRelatedData('lezione_conoscenze', 'conoscenza_id', $this->conoscenze);
            $this->syncRelatedData('lezione_abilita', 'abilita_id', $this->abilita);

            // Sync UDA relationship to pivot table for synoptic view
            if ($this->uda_id) {
                $this->syncRelatedData('uda_lessons', 'uda_id', [$this->uda_id], 'lesson_id');
            } else {
                $this->syncRelatedData('uda_lessons', 'uda_id', [], 'lesson_id');
            }

            // After all relationships are synced, trigger the course year recalculation
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
     * Loads related data.
     */
    private function loadRelatedData()
    {
        // Load conoscenze
        $stmt = $this->conn->prepare('SELECT conoscenza_id FROM lezione_conoscenze WHERE lezione_id = :id');
        $stmt->execute(['id' => $this->id]);
        $this->conoscenze = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Load abilita
        $stmt = $this->conn->prepare('SELECT abilita_id FROM lezione_abilita WHERE lezione_id = :id');
        $stmt->execute(['id' => $this->id]);
        $this->abilita = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * A generic helper to sync many-to-many relationships.
     */
    private function syncRelatedData($tableName, $relatedIdColumn, $relatedIds, $thisIdColumn = 'lezione_id')
    {
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

    /**
     * Delete a lesson by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare('DELETE FROM lessons WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Find all lessons for a given UDA.
     *
     * @param int $udaId
     * @return Lesson[]
     */
    public function findByUdaId($udaId)
    {
        $stmt = $this->conn->prepare('SELECT * FROM lessons WHERE uda_id = :uda_id ORDER BY updated_at DESC');
        $stmt->execute(['uda_id' => $udaId]);

        $lessonsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lessons = [];
        foreach ($lessonsData as $data) {
            $lessons[] = new self($this->conn, $data);
        }
        return $lessons;
    }

    /**
     * Search for lessons by content and tags with pagination.
     *
     * @param string $contentTerm
     * @param string $tagsTerm
     * @param int $limit
     * @param int $offset
     * @return Lesson[]
     */
    public function search($contentTerm, $tagsTerm, $limit, $offset)
    {
        list($sql, $params) = $this->buildSearchQuery('SELECT *', $contentTerm, $tagsTerm);

        $sql .= ' ORDER BY updated_at DESC LIMIT :limit OFFSET :offset';
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->conn->prepare($sql);

        // Bind parameters dynamically
        foreach ($params as $key => &$val) {
            $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindParam($key, $val, $type);
        }

        $stmt->execute();

        $lessonsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lessons = [];
        foreach ($lessonsData as $data) {
            $lessons[] = new self($this->conn, $data);
        }
        return $lessons;
    }

    /**
     * Count search results.
     *
     * @param string $contentTerm
     * @param string $tagsTerm
     * @return int
     */
    public function countSearch($contentTerm, $tagsTerm)
    {
        list($sql, $params) = $this->buildSearchQuery('SELECT COUNT(id)', $contentTerm, $tagsTerm);

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Helper to build the search query.
     */
    private function buildSearchQuery($select, $contentTerm, $tagsTerm)
    {
        $sql = $select . ' FROM lessons';
        $where = [];
        $params = [];

        if (!empty($contentTerm)) {
            $where[] = 'content LIKE :content';
            $params[':content'] = '%' . $contentTerm . '%';
        }

        if (!empty($tagsTerm)) {
            $where[] = 'tags LIKE :tags';
            $params[':tags'] = '%' . $tagsTerm . '%';
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return [$sql, $params];
    }

    /**
     * Save student-specific data for a lesson.
     *
     * @param int $userId
     * @param int $lessonId
     * @param string $type
     * @param mixed $data
     * @return bool|string True on success, error message on failure.
     */
    public function saveStudentData($userId, $lessonId, $type, $data)
    {
        $sql = 'INSERT INTO student_lesson_data (user_id, lesson_id, type, data) VALUES (:user_id, :lesson_id, :type, :data)';

        try {
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                'user_id' => $userId,
                'lesson_id' => $lessonId,
                'type' => $type,
                'data' => json_encode($data)
            ]);

            if ($result) {
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                return "DB Error: " . ($errorInfo[2] ?? 'Unknown error');
            }
        } catch (PDOException $e) {
            return "DB Error: " . $e->getMessage();
        }
    }

    /**
     * Get all student-specific data for a lesson.
     *
     * @param int $userId
     * @param int $lessonId
     * @return array
     */
    public function getStudentData($userId, $lessonId)
    {
        $stmt = $this->conn->prepare('SELECT * FROM student_lesson_data WHERE user_id = :user_id AND lesson_id = :lesson_id ORDER BY created_at ASC');
        $stmt->execute(['user_id' => $userId, 'lesson_id' => $lessonId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode the JSON data for each record
        foreach ($results as &$row) {
            $row['data'] = json_decode($row['data'], true);
        }

        return $results;
    }

    /**
     * Delete a specific piece of student data.
     *
     * @param int $userId
     * @param int $dataId
     * @return bool|string True on success, error message on failure.
     */
    public function deleteStudentData($userId, $dataId)
    {
        // We include user_id in the WHERE clause to ensure a user can only delete their own data.
        $sql = 'DELETE FROM student_lesson_data WHERE id = :id AND user_id = :user_id';

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'id' => $dataId,
                'user_id' => $userId
            ]);

            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                // Either the ID didn't exist or it didn't belong to the user.
                return "Data not found or permission denied.";
            }
        } catch (PDOException $e) {
            return "DB Error: " . $e->getMessage();
        }
    }

    /**
     * Get all unique students who have submitted data for a specific lesson.
     *
     * @param int $lessonId
     * @return array
     */
    public function getStudentsForLesson($lessonId)
    {
        $stmt = $this->conn->prepare('
            SELECT DISTINCT u.id, u.username, u.classe, u.corso, u.anno_scolastico
            FROM student_lesson_data sld
            JOIN users u ON sld.user_id = u.id
            WHERE sld.lesson_id = :lesson_id
            ORDER BY u.username ASC
        ');
        $stmt->execute(['lesson_id' => $lessonId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all student-specific data for a given lesson.
     *
     * @param int $lessonId
     * @return array
     */
    public function getAllStudentDataForLesson($lessonId)
    {
        $stmt = $this->conn->prepare('
            SELECT sld.id, sld.user_id, u.username, sld.type, sld.data, sld.created_at
            FROM student_lesson_data sld
            JOIN users u ON sld.user_id = u.id
            WHERE sld.lesson_id = :lesson_id
            ORDER BY u.username ASC, sld.created_at ASC
        ');
        $stmt->execute(['lesson_id' => $lessonId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode the JSON data for each record
        foreach ($results as &$row) {
            $row['data'] = json_decode($row['data'], true);
        }

        return $results;
    }
}
