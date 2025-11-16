-- Migration Script: Manager and HR Leave Application System
-- This script extends the leaves and leave_credits tables to support Manager and HR leave applications
-- Approval workflow: Employee → Manager, Manager → HR, HR → Owner

-- ============================================
-- 1. UPDATE LEAVES TABLE
-- ============================================

-- Add applicant columns for Manager and HR
ALTER TABLE `leaves` 
  ADD COLUMN IF NOT EXISTS `applicant_manager_id` INT(11) NULL AFTER `employee_id`,
  ADD COLUMN IF NOT EXISTS `applicant_hr_id` INT(11) NULL AFTER `applicant_manager_id`,
  ADD COLUMN IF NOT EXISTS `applicant_type` ENUM('employee', 'manager', 'hr') DEFAULT 'employee' AFTER `applicant_hr_id`;

-- Add approver columns (HR approves Manager, Owner approves HR)
ALTER TABLE `leaves`
  ADD COLUMN IF NOT EXISTS `approver_hr_id` INT(11) NULL AFTER `manager_id`,
  ADD COLUMN IF NOT EXISTS `approver_owner_id` INT(11) NULL AFTER `approver_hr_id`,
  ADD COLUMN IF NOT EXISTS `approver_type` ENUM('manager', 'hr', 'owner') NULL AFTER `approver_owner_id`,
  ADD COLUMN IF NOT EXISTS `approver_name` VARCHAR(255) NULL AFTER `approver_type`;

-- Add indexes for better query performance
ALTER TABLE `leaves`
  ADD INDEX IF NOT EXISTS `idx_leaves_applicant_manager` (`applicant_manager_id`),
  ADD INDEX IF NOT EXISTS `idx_leaves_applicant_hr` (`applicant_hr_id`),
  ADD INDEX IF NOT EXISTS `idx_leaves_applicant_type` (`applicant_type`),
  ADD INDEX IF NOT EXISTS `idx_leaves_approver_hr` (`approver_hr_id`),
  ADD INDEX IF NOT EXISTS `idx_leaves_approver_owner` (`approver_owner_id`),
  ADD INDEX IF NOT EXISTS `idx_leaves_approver_type` (`approver_type`),
  ADD INDEX IF NOT EXISTS `idx_leaves_status_applicant` (`status`, `applicant_type`);

-- Add foreign key constraints
-- Note: Using IF NOT EXISTS pattern - if constraint exists, it will be skipped
SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'leaves'
    AND CONSTRAINT_NAME = 'fk_leaves_applicant_manager'
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE `leaves` ADD CONSTRAINT `fk_leaves_applicant_manager` FOREIGN KEY (`applicant_manager_id`) REFERENCES `managers` (`id`) ON DELETE SET NULL',
    'SELECT "Constraint fk_leaves_applicant_manager already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'leaves'
    AND CONSTRAINT_NAME = 'fk_leaves_applicant_hr'
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE `leaves` ADD CONSTRAINT `fk_leaves_applicant_hr` FOREIGN KEY (`applicant_hr_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL',
    'SELECT "Constraint fk_leaves_applicant_hr already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'leaves'
    AND CONSTRAINT_NAME = 'fk_leaves_approver_hr'
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE `leaves` ADD CONSTRAINT `fk_leaves_approver_hr` FOREIGN KEY (`approver_hr_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL',
    'SELECT "Constraint fk_leaves_approver_hr already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'leaves'
    AND CONSTRAINT_NAME = 'fk_leaves_approver_owner'
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE `leaves` ADD CONSTRAINT `fk_leaves_approver_owner` FOREIGN KEY (`approver_owner_id`) REFERENCES `owners` (`id`) ON DELETE SET NULL',
    'SELECT "Constraint fk_leaves_approver_owner already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing records to set applicant_type
UPDATE `leaves` SET `applicant_type` = 'employee' WHERE `applicant_type` IS NULL OR `applicant_type` = '';

-- ============================================
-- 2. UPDATE LEAVE_CREDITS TABLE
-- ============================================

-- Add support for Manager and HR leave credits
ALTER TABLE `leave_credits`
  ADD COLUMN IF NOT EXISTS `manager_id` INT(11) NULL AFTER `employee_id`,
  ADD COLUMN IF NOT EXISTS `hr_id` INT(11) NULL AFTER `manager_id`,
  ADD COLUMN IF NOT EXISTS `user_type` ENUM('employee', 'manager', 'hr') DEFAULT 'employee' AFTER `hr_id`;

-- Add indexes
ALTER TABLE `leave_credits`
  ADD INDEX IF NOT EXISTS `idx_leave_credits_manager_id` (`manager_id`),
  ADD INDEX IF NOT EXISTS `idx_leave_credits_hr_id` (`hr_id`),
  ADD INDEX IF NOT EXISTS `idx_leave_credits_user_type` (`user_type`),
  ADD INDEX IF NOT EXISTS `idx_leave_credits_user_lookup` (`user_type`, `employee_id`, `manager_id`, `hr_id`);

-- Add foreign key constraints for leave_credits
SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'leave_credits'
    AND CONSTRAINT_NAME = 'fk_leave_credits_manager'
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE `leave_credits` ADD CONSTRAINT `fk_leave_credits_manager` FOREIGN KEY (`manager_id`) REFERENCES `managers` (`id`) ON DELETE CASCADE',
    'SELECT "Constraint fk_leave_credits_manager already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'leave_credits'
    AND CONSTRAINT_NAME = 'fk_leave_credits_hr'
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE `leave_credits` ADD CONSTRAINT `fk_leave_credits_hr` FOREIGN KEY (`hr_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE',
    'SELECT "Constraint fk_leave_credits_hr already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing records to set user_type
UPDATE `leave_credits` SET `user_type` = 'employee' WHERE `user_type` IS NULL OR `user_type` = '';

-- ============================================
-- 3. VERIFICATION QUERIES
-- ============================================

-- Verify leaves table structure
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'leaves'
AND COLUMN_NAME IN ('applicant_manager_id', 'applicant_hr_id', 'applicant_type', 'approver_hr_id', 'approver_owner_id', 'approver_type', 'approver_name')
ORDER BY ORDINAL_POSITION;

-- Verify leave_credits table structure
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'leave_credits'
AND COLUMN_NAME IN ('manager_id', 'hr_id', 'user_type')
ORDER BY ORDINAL_POSITION;

