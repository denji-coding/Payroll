<?php 

/**
 * Base Model Class
 * Provides common database operations for all models
 */
class Model
{
    protected $table;
    protected $conn;
    protected $allowed_columns = [];

    /**
     * Constructor
     * @param PDO $dbConnection Database connection
     */
    public function __construct($dbConnection = null)
    {
        if ($dbConnection) {
            $this->conn = $dbConnection;
        } else {
            // Create new database connection if none provided
            require_once __DIR__ . '/database.php';
            $db = new Database();
            $this->conn = $db->getConnection();
        }
    }

    /**
     * Get all records from table
     * @param int $limit Limit number of records
     * @param int $offset Offset for pagination
     * @param string $order Order direction (ASC/DESC)
     * @param string $orderColumn Column to order by
     * @return array Array of records
     */
    public function getAll($limit = null, $offset = 0, $order = 'DESC', $orderColumn = 'id')
    {
        $query = "SELECT * FROM {$this->table}";
        
        if ($limit) {
            $query .= " ORDER BY {$orderColumn} {$order} LIMIT {$limit} OFFSET {$offset}";
        } else {
            $query .= " ORDER BY {$orderColumn} {$order}";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find record by ID
     * @param int $id Record ID
     * @return array|false Record data or false if not found
     */
    public function findById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find records by conditions
     * @param array $conditions Array of conditions [column => value]
     * @param int $limit Limit number of records
     * @param int $offset Offset for pagination
     * @param string $order Order direction (ASC/DESC)
     * @param string $orderColumn Column to order by
     * @return array Array of records
     */
    public function where($conditions, $limit = null, $offset = 0, $order = 'DESC', $orderColumn = 'id')
    {
        $keys = array_keys($conditions);
        $placeholders = array_map(function($key) {
            return "{$key} = :{$key}";
        }, $keys);
        
        $query = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $placeholders);
        
        if ($limit) {
            $query .= " ORDER BY {$orderColumn} {$order} LIMIT {$limit} OFFSET {$offset}";
        } else {
            $query .= " ORDER BY {$orderColumn} {$order}";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($conditions);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find first record by conditions
     * @param array $conditions Array of conditions [column => value]
     * @return array|false Record data or false if not found
     */
    public function first($conditions)
    {
        $result = $this->where($conditions, 1);
        return $result ? $result[0] : false;
    }

    /**
     * Insert new record
     * @param array $data Record data
     * @return int|false Inserted record ID or false on failure
     */
    public function insert($data)
    {
        // Filter data to only allowed columns
        $cleanData = $this->getAllowedColumns($data);
        
        $keys = array_keys($cleanData);
        $placeholders = array_map(function($key) {
            return ":{$key}";
        }, $keys);
        
        $query = "INSERT INTO {$this->table} (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute($cleanData);
        
        return $result ? $this->conn->lastInsertId() : false;
    }

    /**
     * Update record by ID
     * @param int $id Record ID
     * @param array $data Update data
     * @return bool Success status
     */
    public function update($id, $data)
    {
        // Filter data to only allowed columns
        $cleanData = $this->getAllowedColumns($data);
        
        $keys = array_keys($cleanData);
        $setClause = array_map(function($key) {
            return "{$key} = :{$key}";
        }, $keys);
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = :id";
        $cleanData['id'] = $id;
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($cleanData);
    }

    /**
     * Delete record by ID (soft delete if deleted_at column exists)
     * @param int $id Record ID
     * @return bool Success status
     */
    public function delete($id)
    {
        // Check if table has deleted_at column for soft delete
        $stmt = $this->conn->prepare("DESCRIBE {$this->table}");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('deleted_at', $columns)) {
            // Soft delete
            $query = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?";
        } else {
            // Hard delete
            $query = "DELETE FROM {$this->table} WHERE id = ?";
        }
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Filter data to only allowed columns
     * @param array $data Input data
     * @return array Filtered data
     */
    protected function getAllowedColumns($data)
    {
        if (empty($this->allowed_columns)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->allowed_columns));
    }

    /**
     * Count total records
     * @param array $conditions Optional conditions
     * @return int Count of records
     */
    public function count($conditions = [])
    {
        if (empty($conditions)) {
            $query = "SELECT COUNT(*) FROM {$this->table}";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
        } else {
            $keys = array_keys($conditions);
            $placeholders = array_map(function($key) {
                return "{$key} = :{$key}";
            }, $keys);
            
            $query = "SELECT COUNT(*) FROM {$this->table} WHERE " . implode(' AND ', $placeholders);
            $stmt = $this->conn->prepare($query);
            $stmt->execute($conditions);
        }
        
        return (int) $stmt->fetchColumn();
    }

    /**
     * Execute raw query
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array|bool Query result
     */
    public function query($query, $params = [])
    {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        if (stripos($query, 'SELECT') === 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $stmt->rowCount();
	}
}
	


