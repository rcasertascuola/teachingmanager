<?php

class Exercise
{
    public $id;
    public $title;
    public $type;
    public $content;
    public $options; // JSON format
    public $enabled;
    public $created_at;
    public
    $updated_at;

    public function __construct($data)
    {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? '';
        $this->type = $data['type'] ?? 'open_answer';
        $this->content = $data['content'] ?? '';
        $this->options = $data['options'] ?? '{}';
        $this->enabled = $data['enabled'] ?? 0;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Find a single exercise by its ID.
     *
     * @param int $id
     * @return Exercise|null
     */
    public static function findById($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM exercises WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new self($data);
        }
        return null;
    }

    /**
     * Find all exercises with pagination.
     *
     * @param int $limit
     * @param int $offset
     * @return Exercise[]
     */
    public static function findAll($limit, $offset)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM exercises ORDER BY updated_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $exercisesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $exercises = [];
        foreach ($exercisesData as $data) {
            $exercises[] = new self($data);
        }
        return $exercises;
    }

    /**
     * Count all exercises.
     * @return int
     */
    public static function countAll()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        return (int) $pdo->query('SELECT COUNT(id) FROM exercises')->fetchColumn();
    }

    /**
     * Save the exercise (insert or update).
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
            // Update existing exercise
            $sql = 'UPDATE exercises SET title = :title, type = :type, content = :content, options = :options, enabled = :enabled WHERE id = :id';
            $params = [
                'id' => $this->id,
                'title' => $this->title,
                'type' => $this->type,
                'content' => $this->content,
                'options' => $this->options,
                'enabled' => $this->enabled,
            ];
        } else {
            // Insert new exercise
            $sql = 'INSERT INTO exercises (title, type, content, options, enabled) VALUES (:title, :type, :content, :options, :enabled)';
            $params = [
                'title' => $this->title,
                'type' => $this->type,
                'content' => $this->content,
                'options' => $this->options,
                'enabled' => $this->enabled,
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
     * Delete an exercise by its ID.
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        // The foreign key constraint with ON DELETE CASCADE will handle deleting from `exercise_lesson` and `student_exercise_answers`.
        $stmt = $pdo->prepare('DELETE FROM exercises WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Get all lessons linked to this exercise.
     *
     * @return array
     */
    public function getLinkedLessons()
    {
        if (!$this->id) {
            return [];
        }

        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('
            SELECT l.id, l.title
            FROM lessons l
            JOIN exercise_lesson el ON l.id = el.lesson_id
            WHERE el.exercise_id = :exercise_id
        ');
        $stmt->execute(['exercise_id' => $this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update the lessons linked to this exercise.
     *
     * @param int[] $lessonIds
     * @return bool
     */
    public function updateLinkedLessons($lessonIds)
    {
        if (!$this->id) {
            return false;
        }

        $database = new Database();
        $pdo = $database->getConnection();

        // Start transaction
        $pdo->beginTransaction();

        try {
            // Delete existing links
            $deleteStmt = $pdo->prepare('DELETE FROM exercise_lesson WHERE exercise_id = :exercise_id');
            $deleteStmt->execute(['exercise_id' => $this->id]);

            // Insert new links
            if (!empty($lessonIds)) {
                $insertSql = 'INSERT INTO exercise_lesson (exercise_id, lesson_id) VALUES (:exercise_id, :lesson_id)';
                $insertStmt = $pdo->prepare($insertSql);

                foreach ($lessonIds as $lessonId) {
                    $insertStmt->execute([
                        'exercise_id' => $this->id,
                        'lesson_id' => $lessonId
                    ]);
                }
            }

            // Commit transaction
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            // In a real app, you'd log this error
            return false;
        }
    }

    /**
     * Save a student's answer for an exercise.
     *
     * @param int $userId
     * @param int $exerciseId
     * @param mixed $answerData
     * @return bool|string True on success, error message on failure.
     */
    public static function saveStudentAnswer($userId, $exerciseId, $answerData)
    {
        $database = new Database();
        $pdo = $database->getConnection();

        // For simplicity, we are overwriting any previous answer.
        // A more complex implementation might version the answers.
        $sql = '
            INSERT INTO student_exercise_answers (user_id, exercise_id, answer)
            VALUES (:user_id, :exercise_id, :answer)
            ON DUPLICATE KEY UPDATE answer = VALUES(answer), updated_at = CURRENT_TIMESTAMP
        ';

        // We need to check if a record for this user and exercise already exists to do an insert or update.
        // A simpler approach for now is to just insert. A unique constraint on (user_id, exercise_id) would be better.
        // Let's add that unique constraint to the table and use ON DUPLICATE KEY UPDATE.
        // ALTER TABLE student_exercise_answers ADD UNIQUE KEY `user_exercise_unique` (`user_id`, `exercise_id`);

        $sql = '
            INSERT INTO student_exercise_answers (user_id, exercise_id, answer, score, corrected_by)
            VALUES (:user_id, :exercise_id, :answer, NULL, NULL)
            ON DUPLICATE KEY UPDATE answer = VALUES(answer), updated_at = CURRENT_TIMESTAMP, score = NULL, corrected_by = NULL;
        ';


        try {
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'user_id' => $userId,
                'exercise_id' => $exerciseId,
                'answer' => json_encode($answerData)
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
     * Get all student answers for a specific exercise.
     *
     * @param int $exerciseId
     * @return array
     */
    public static function getStudentAnswers($exerciseId)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('
            SELECT sa.*, u.username
            FROM student_exercise_answers sa
            JOIN users u ON sa.user_id = u.id
            WHERE sa.exercise_id = :exercise_id
            ORDER BY u.username ASC
        ');
        $stmt->execute(['exercise_id' => $exerciseId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode the JSON answer data for each record
        foreach ($results as &$row) {
            $row['answer'] = json_decode($row['answer'], true);
        }

        return $results;
    }

    /**
     * Save the correction (score) for a specific student answer.
     *
     * @param int $answerId
     * @param float $score
     * @param int $teacherId
     * @return bool
     */
    public static function saveCorrection($answerId, $score, $teacherId)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $sql = '
            UPDATE student_exercise_answers
            SET score = :score, corrected_by = :corrected_by, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ';

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id' => $answerId,
            'score' => $score,
            'corrected_by' => $teacherId
        ]);
    }

    /**
     * Find all enabled exercises for a specific lesson.
     *
     * @param int $lessonId
     * @return Exercise[]
     */
    public static function findForLesson($lessonId)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare('
            SELECT e.* FROM exercises e
            JOIN exercise_lesson el ON e.id = el.exercise_id
            WHERE el.lesson_id = :lesson_id AND e.enabled = 1
            ORDER BY e.title ASC
        ');
        $stmt->execute(['lesson_id' => $lessonId]);

        $exercisesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $exercises = [];
        foreach ($exercisesData as $data) {
            $exercises[] = new self($data);
        }
        return $exercises;
    }
}
