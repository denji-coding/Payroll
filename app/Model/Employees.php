<?php

require_once __DIR__ . '/../core/Model.php';

class Employees extends Model
{
    protected $table = "employees";
    protected $conn; // ✅ define connection property

    protected $allowed_columns = [
        'employee_no', 'rfid_number', 'first_name', 'middle_name', 'last_name', 'dob',
        'place_of_birth', 'sex', 'civil_status', 'contact_number', 'email',
        'citizenship', 'blood_type', 'position', 'address', 'base_salary',
        'sss_number', 'pagibig_number', 'philhealth_number', 'branch_manager',
        'photo_path'
    ];

    // ✅ Constructor to receive and store DB connection
    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    /**
     * Get all employees not soft-deleted
     */
    public function getAllEmployees()
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
