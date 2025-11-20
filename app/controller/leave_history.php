<?php
require_once '../app/core/session_helper.php';

// Check if admin is logged in
requireAdminAuth();

// Log user activity
logUserActivity('Access leave history page');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/core/database.php';

$db = new Database();
$pdo = $db->getConnection();

// Get leave applications for employees, managers, and HR using UNION
$sql = "
    -- Employee Leaves
    SELECT 
        e.photo_path,
        e.employee_no,
        CONCAT(
            UPPER(LEFT(e.first_name, 1)), LOWER(SUBSTRING(e.first_name, 2)), ' ',
            UPPER(LEFT(e.middle_name, 1)), '. ',
            UPPER(LEFT(e.last_name, 1)), LOWER(SUBSTRING(e.last_name, 2))
        ) AS employee_name,
        e.sex,
        l.id AS leave_id,
        l.leave_type,
        l.start_date,
        l.end_date,
        l.status,
        CONCAT(
            UPPER(LEFT(m.m_first_name, 1)), LOWER(SUBSTRING(m.m_first_name, 2)), ' ',
            UPPER(LEFT(m.m_middle_name, 1)), '. ',
            UPPER(LEFT(m.m_last_name, 1)), LOWER(SUBSTRING(m.m_last_name, 2))
        ) AS manager_name,
        lr.reason AS rejection_reason,
        l.created_at,
        l.updated_at,
        'employee' AS applicant_type
    FROM leaves l
    JOIN employees e ON l.employee_id = e.id
    LEFT JOIN managers m ON l.manager_id = m.id
    LEFT JOIN leave_rejections lr ON l.id = lr.leave_id
    WHERE l.employee_id IS NOT NULL AND (l.applicant_type IS NULL OR l.applicant_type = 'employee')
    
    UNION ALL
    
    -- Manager Leaves
    SELECT 
        m2.m_photo_path AS photo_path,
        CONCAT('MGR-', m2.id) AS employee_no,
        CONCAT(
            UPPER(LEFT(m2.m_first_name, 1)), LOWER(SUBSTRING(m2.m_first_name, 2)), ' ',
            UPPER(LEFT(m2.m_middle_name, 1)), '. ',
            UPPER(LEFT(m2.m_last_name, 1)), LOWER(SUBSTRING(m2.m_last_name, 2))
        ) AS employee_name,
        m2.m_sex AS sex,
        l.id AS leave_id,
        l.leave_type,
        l.start_date,
        l.end_date,
        l.status,
        CASE 
            WHEN l.approver_type = 'hr' AND a.id IS NOT NULL THEN
                CONCAT(
                    UPPER(LEFT(a.hr_first_name, 1)), LOWER(SUBSTRING(a.hr_first_name, 2)), ' ',
                    UPPER(LEFT(a.hr_middle_name, 1)), '. ',
                    UPPER(LEFT(a.hr_last_name, 1)), LOWER(SUBSTRING(a.hr_last_name, 2))
                )
            ELSE NULL
        END AS manager_name,
        lr.reason AS rejection_reason,
        l.created_at,
        l.updated_at,
        'manager' AS applicant_type
    FROM leaves l
    JOIN managers m2 ON l.applicant_manager_id = m2.id
    LEFT JOIN admins a ON l.approver_hr_id = a.id AND a.deleted_at IS NULL
    LEFT JOIN leave_rejections lr ON l.id = lr.leave_id
    WHERE l.applicant_manager_id IS NOT NULL AND l.applicant_type = 'manager'
    
    UNION ALL
    
    -- HR Leaves
    SELECT 
        a2.hr_photo_path AS photo_path,
        a2.hr_employee_id AS employee_no,
        CONCAT(
            UPPER(LEFT(a2.hr_first_name, 1)), LOWER(SUBSTRING(a2.hr_first_name, 2)), ' ',
            UPPER(LEFT(a2.hr_middle_name, 1)), '. ',
            UPPER(LEFT(a2.hr_last_name, 1)), LOWER(SUBSTRING(a2.hr_last_name, 2))
        ) AS employee_name,
        a2.hr_sex AS sex,
        l.id AS leave_id,
        l.leave_type,
        l.start_date,
        l.end_date,
        l.status,
        CASE 
            WHEN o.id IS NOT NULL THEN o.name
            ELSE NULL
        END AS manager_name,
        lr.reason AS rejection_reason,
        l.created_at,
        l.updated_at,
        'hr' AS applicant_type
    FROM leaves l
    JOIN admins a2 ON l.applicant_hr_id = a2.id
    LEFT JOIN owners o ON l.approver_owner_id = o.id
    LEFT JOIN leave_rejections lr ON l.id = lr.leave_id
    WHERE l.applicant_hr_id IS NOT NULL AND l.applicant_type = 'hr'
    
    ORDER BY created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

require views_path("auth/leave_history");
?>