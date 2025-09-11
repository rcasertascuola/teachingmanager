<?php
header('Content-Type: application/json');

try {
    require_once '../src/Database.php';

    $database = new Database();
    $db = $database->getConnection();

    // --- Get request parameters ---
    $params = json_decode(file_get_contents('php://input'), true);

    $table = $params['table'] ?? '';
    $columns = $params['columns'] ?? [];
    $filters = $params['filters'] ?? [];
    $sortCol = $params['sortCol'] ?? 'id';
    $sortDir = $params['sortDir'] ?? 'ASC';
    $page = isset($params['page']) ? (int)$params['page'] : 1;
    $pageSize = isset($params['pageSize']) ? (int)$params['pageSize'] : 10;

    // --- Basic validation ---
    if (empty($table) || empty($columns)) {
        throw new InvalidArgumentException('Table and columns must be specified.');
    }

    // Whitelist sort directions
    $sortDir = strtoupper($sortDir);
    if (!in_array($sortDir, ['ASC', 'DESC'])) {
        $sortDir = 'ASC';
    }

    // Whitelist sortable columns
    if (!in_array($sortCol, $columns)) {
        $sortCol = 'id'; // Default sort column
    }

    // --- Get additional parameters for complex queries ---
    $joins = $params['joins'] ?? [];
    $selects = $params['selects'] ?? ['*'];

    // --- Build the query ---
    $selectClause = implode(', ', $selects);
    $query = "SELECT $selectClause FROM " . $table;
    $countQuery = "SELECT count(*) as total FROM " . $table;

    // Add joins
    if (!empty($joins)) {
        $joinClause = implode(' ', $joins);
        $query .= ' ' . $joinClause;
        $countQuery .= ' ' . $joinClause;
    }

    $whereClauses = [];
    $bindings = [];

    // Build a map of aliases to real column names for use in WHERE clauses
    $columnMap = [];
    foreach ($selects as $select) {
        $select = trim($select);
        $alias = '';
        $realName = '';

        if (preg_match('/^(.*)\s+as\s+(\w+)$/i', $select, $matches)) {
            $realName = trim($matches[1]);
            $alias = trim($matches[2]);
        } else {
            $parts = explode('.', $select);
            $alias = trim(end($parts));
            $realName = $select;
        }
        $columnMap[$alias] = $realName;
    }

    if (!empty($filters)) {
        foreach ($filters as $key => $value) {
            if (!empty($value) && in_array($key, $columns)) {
                $realColumn = $columnMap[$key] ?? $key;
                $whereClauses[] = "$realColumn LIKE :$key";
                $bindings[":$key"] = "%$value%";
            }
        }
    }

    if (!empty($whereClauses)) {
        $query .= " WHERE " . implode(' AND ', $whereClauses);
        $countQuery .= " WHERE " . implode(' AND ', $whereClauses);
    }

    // --- Get total count for pagination ---
    $countStmt = $db->prepare($countQuery);
    foreach ($bindings as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // --- Add sorting and pagination to the main query ---
    $query .= " ORDER BY $sortCol $sortDir";

    $offset = ($page - 1) * $pageSize;
    $query .= " LIMIT :pagesize OFFSET :offset";

    // --- Execute main query ---
    $stmt = $db->prepare($query);

    foreach ($bindings as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(':pagesize', $pageSize, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Return JSON response ---
    echo json_encode([
        'data' => $data,
        'pagination' => [
            'page' => $page,
            'pageSize' => $pageSize,
            'totalRecords' => (int)$totalRecords,
            'totalPages' => ceil($totalRecords / $pageSize)
        ]
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'A server error occurred.',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    exit;
}
?>
