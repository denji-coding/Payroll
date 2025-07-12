<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


class Database
{
    private function db_connect()
    {
        $DBHOST   = "localhost";
        $DBNAME   = "payroll_db";
        $DBUSER   = "root";
        $DBPASS   = "";
        $DBDRIVER = "mysql";

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
            throw new Exception("Database connection failed");
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