-- Migration: Role-Based Access Control System
-- This migration creates the roles table and adds role_id to employees, managers, and admins tables
-- Date: 2025-11-15
-- IMPORTANT: Run this SQL file in your database before using the new role-based system

-- Step 1: Create roles table
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `can_access_employee_portal` tinyint(1) DEFAULT 0,
  `can_access_manager_portal` tinyint(1) DEFAULT 0,
  `can_access_hr_portal` tinyint(1) DEFAULT 0,
  `can_access_owner_portal` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Step 2: Insert default roles (ignore duplicates)
INSERT IGNORE INTO `roles` (`id`, `name`, `description`, `can_access_employee_portal`, `can_access_manager_portal`, `can_access_hr_portal`, `can_access_owner_portal`) VALUES
(1, 'Employee', 'Regular employee with basic access', 1, 0, 0, 0),
(2, 'Manager', 'Branch manager with employee and manager portal access', 1, 1, 0, 0),
(3, 'HR', 'Human Resources with employee and HR portal access', 1, 0, 1, 0),
(4, 'HR Manager', 'HR personnel who can also manage branches', 1, 1, 1, 0),
(5, 'Supervisor', 'Supervisor role with employee and manager access', 1, 1, 0, 0),
(6, 'Owner', 'System owner with full access', 1, 1, 1, 1);

-- Step 3: Add role_id to employees table (check if column exists first)
SET @dbname = DATABASE();
SET @tablename = 'employees';
SET @columnname = 'role_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' int(11) DEFAULT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for employees role_id
ALTER TABLE `employees` ADD KEY `fk_employee_role` (`role_id`);

-- Set default role for existing employees (Employee role = 1)
UPDATE `employees` SET `role_id` = 1 WHERE `role_id` IS NULL;

-- Add foreign key constraint for employees (if not exists)
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (CONSTRAINT_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (CONSTRAINT_NAME = 'fk_employee_role')
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD CONSTRAINT `fk_employee_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Step 4: Add role_id to managers table
SET @tablename = 'managers';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' int(11) DEFAULT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for managers role_id
ALTER TABLE `managers` ADD KEY `fk_manager_role` (`role_id`);

-- Set default role for existing managers (Manager role = 2)
UPDATE `managers` SET `role_id` = 2 WHERE `role_id` IS NULL;

-- Add foreign key constraint for managers (if not exists)
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (CONSTRAINT_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (CONSTRAINT_NAME = 'fk_manager_role')
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD CONSTRAINT `fk_manager_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Step 5: Add role_id to admins table
SET @tablename = 'admins';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' int(11) DEFAULT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for admins role_id
ALTER TABLE `admins` ADD KEY `fk_admin_role` (`role_id`);

-- Set default role for existing HR/admins (HR role = 3)
UPDATE `admins` SET `role_id` = 3 WHERE `role_id` IS NULL;

-- Add foreign key constraint for admins (if not exists)
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (CONSTRAINT_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (CONSTRAINT_NAME = 'fk_admin_role')
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD CONSTRAINT `fk_admin_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
