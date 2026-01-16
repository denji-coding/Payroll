<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration
require_once __DIR__ . '/db_config.php';

class Database
{
    private function db_connect()
    {
        // Check if we're on InfinityFree hosting
        $isInfinityFree = (strpos($_SERVER['HTTP_HOST'] ?? '', 'free.nf') !== false) || 
                          (strpos($_SERVER['HTTP_HOST'] ?? '', 'epizy.com') !== false) ||
                          (strpos($_SERVER['HTTP_HOST'] ?? '', 'infinityfree.net') !== false);
        
        if ($isInfinityFree) {
            // InfinityFree database credentials
            $DBHOST   = INFINITYFREE_DB_HOST;
            $DBNAME   = INFINITYFREE_DB_NAME;
            $DBUSER   = INFINITYFREE_DB_USER;
            $DBPASS   = INFINITYFREE_DB_PASS;
            $DBDRIVER = "mysql";
        } else {
            // Local development credentials
            $DBHOST   = LOCAL_DB_HOST;
            $DBNAME   = LOCAL_DB_NAME;
            $DBUSER   = LOCAL_DB_USER;
            $DBPASS   = LOCAL_DB_PASS;
            $DBDRIVER = "mysql";
        }

        try {
            $conn = new PDO("$DBDRIVER:host=$DBHOST;dbname=$DBNAME", $DBUSER, $DBPASS);
            // Set PDO to throw exceptions on error
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Set default fetch mode to associative array
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            // Use prepared statements by default
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $conn;
        }
        catch(PDOException $e) {
            // Log error instead of echoing
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function query($query, $data = array()) 
{
    try {
        $conn = $this->db_connect();
        $stmt = $conn->prepare($query);
        $check = $stmt->execute($data);

        if ($check) {
            // For SELECT statements, return fetched data
            if (stripos(ltrim($query), 'select') === 0) {  // <-- Added ltrim() here
                $result = $stmt->fetchAll();
                return $result ?: []; // Return empty array if no results
            }
            // For INSERT/UPDATE/DELETE, return true
            return true;
        }
        return false;
    }
    catch(PDOException $e) {
        error_log("Query failed: " . $e->getMessage());
        throw new Exception("Database query failed: " . $e->getMessage());

    }
}


    public function getConnection() {
        return $this->db_connect();
    }
    
}