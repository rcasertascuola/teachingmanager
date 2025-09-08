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
     * @return bool
     */
    public function save()
    {
        $database = new Database();
        $pdo = $database->getConnection();

        if ($this->id) {
            // Update existing lesson
            $stmt = $pdo->prepare(
                'UPDATE lessons SET title = :title, content = :content, tags = :tags WHERE id = :id'
            );
            return $stmt->execute([
                'id' => $this->id,
                'title' => $this->title,
                'content' => $this->content,
                'tags' => $this->tags,
            ]);
        } else {
            // Insert new lesson
            $stmt = $pdo->prepare(
                'INSERT INTO lessons (title, content, tags) VALUES (:title, :content, :tags)'
            );
            $result = $stmt->execute([
                'title' => $this->title,
                'content' => $this->content,
                'tags' => $this->tags,
            ]);
            if ($result) {
                $this->id = $pdo->lastInsertId();
            }
            return $result;
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
}
