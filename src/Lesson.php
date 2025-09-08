<?php

class Lesson
{
    public $id;
    public $title;
    public $content;
    public $tags;
    public $created_at;
    public $updated_at;

    public function __construct($data)
    {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? '';
        $this->content = $data['content'] ?? '';
        $this->tags = $data['tags'] ?? '';
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Find all lessons with pagination.
     *
     * @param int $limit
     * @param int $offset
     * @return Lesson[]
     */
    public static function findAll($limit, $offset)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM lessons ORDER BY updated_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $lessonsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lessons = [];
        foreach ($lessonsData as $data) {
            $lessons[] = new self($data);
        }
        return $lessons;
    }

    /**
     * Count all lessons.
     * @return int
     */
    public static function countAll()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        return (int) $pdo->query('SELECT COUNT(id) FROM lessons')->fetchColumn();
    }

    /**
     * Find a single lesson by its ID.
     *
     * @param int $id
     * @return Lesson|null
     */
    public static function findById($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM lessons WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new self($data);
        }
        return null;
    }

    /**
     * Find a single lesson by its exact title.
     *
     * @param string $title
     * @return Lesson|null
     */
    public static function findByTitle($title)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM lessons WHERE title = :title');
        $stmt->execute(['title' => $title]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new self($data);
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
        $database = new Database();
        $pdo = $database->getConnection();

        if (!$pdo) {
            return "Failed to connect to the database.";
        }

        if ($this->id) {
            // Update existing lesson
            $sql = 'UPDATE lessons SET title = :title, content = :content, tags = :tags WHERE id = :id';
            $params = [
                'id' => $this->id,
                'title' => $this->title,
                'content' => $this->content,
                'tags' => $this->tags,
            ];
        } else {
            // Insert new lesson
            $sql = 'INSERT INTO lessons (title, content, tags) VALUES (:title, :content, :tags)';
            $params = [
                'title' => $this->title,
                'content' => $this->content,
                'tags' => $this->tags,
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
            // Return a formatted error message: [SQLSTATE] [Driver Code] Driver Message
            return "DB Error: " . ($errorInfo[2] ?? 'Unknown error');
        }
    }

    /**
     * Delete a lesson by its ID.
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('DELETE FROM lessons WHERE id = :id');
        return $stmt->execute(['id' => $id]);
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
    public static function search($contentTerm, $tagsTerm, $limit, $offset)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        list($sql, $params) = self::buildSearchQuery('SELECT *', $contentTerm, $tagsTerm);

        $sql .= ' ORDER BY updated_at DESC LIMIT :limit OFFSET :offset';
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $pdo->prepare($sql);

        // Bind parameters dynamically
        foreach ($params as $key => &$val) {
            $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindParam($key, $val, $type);
        }

        $stmt->execute();

        $lessonsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lessons = [];
        foreach ($lessonsData as $data) {
            $lessons[] = new self($data);
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
    public static function countSearch($contentTerm, $tagsTerm)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        list($sql, $params) = self::buildSearchQuery('SELECT COUNT(id)', $contentTerm, $tagsTerm);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Helper to build the search query.
     */
    private static function buildSearchQuery($select, $contentTerm, $tagsTerm)
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
    public static function saveStudentData($userId, $lessonId, $type, $data)
    {
        $database = new Database();
        $pdo = $database->getConnection();

        $sql = 'INSERT INTO student_lesson_data (user_id, lesson_id, type, data) VALUES (:user_id, :lesson_id, :type, :data)';

        try {
            $stmt = $pdo->prepare($sql);
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
    public static function getStudentData($userId, $lessonId)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM student_lesson_data WHERE user_id = :user_id AND lesson_id = :lesson_id ORDER BY created_at ASC');
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
    public static function deleteStudentData($userId, $dataId)
    {
        $database = new Database();
        $pdo = $database->getConnection();

        // We include user_id in the WHERE clause to ensure a user can only delete their own data.
        $sql = 'DELETE FROM student_lesson_data WHERE id = :id AND user_id = :user_id';

        try {
            $stmt = $pdo->prepare($sql);
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
    public static function getStudentsForLesson($lessonId)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('
            SELECT DISTINCT u.id, u.username
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
    public static function getAllStudentDataForLesson($lessonId)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('
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
