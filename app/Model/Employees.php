<?php

require_once __DIR__ . '/../core/model.php';

class Employees extends Model
{
    protected $table = "employees";
    protected $allowed_columns = [
        'employee_no', 'rfid_number', 'first_name', 'middle_name', 'last_name', 'dob',
        'place_of_birth', 'sex', 'civil_status', 'contact_number', 'email',
        'citizenship', 'blood_type', 'position', 'address', 'base_salary',
        'sss_number', 'pagibig_number', 'philhealth_number', 'branch_manager',
        'photo_path'
    ];

    /**
     * Constructor to receive and store DB connection
     */
    public function __construct($dbConnection = null)
    {
        parent::__construct($dbConnection);
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

    /**
     * Get employee by employee number
     */
    public function getByEmployeeNo($employeeNo)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE employee_no = ? AND deleted_at IS NULL");
        $stmt->execute([$employeeNo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get employees with manager information
     */
    public function getEmployeesWithManager()
    {
        $stmt = $this->conn->prepare("
            SELECT 
                e.*, 
                CONCAT_WS(' ', m.m_first_name, m.m_middle_name, m.m_last_name) AS manager_name,
                m.m_branch AS branch_name
            FROM employees e
            LEFT JOIN managers m ON e.branch_manager = m.id
            WHERE e.deleted_at IS NULL AND e.approved_by_manager = 1
            ORDER BY e.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get employee with manager information by employee number
     */
    public function getEmployeeWithManager($employeeNo)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                e.*, 
                CONCAT_WS(' ', m.m_first_name, m.m_middle_name, m.m_last_name) AS manager_name,
                m.m_branch AS branch_name
            FROM employees e
            LEFT JOIN managers m ON e.branch_manager = m.id
            WHERE e.employee_no = ? AND e.deleted_at IS NULL
        ");
        $stmt->execute([$employeeNo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
