# Role-Based Access Control System - Migration Guide

## Overview
This migration implements a role-based access control system that allows managers and HR personnel to access the employee portal based on their role permissions.

## What This Migration Does

1. **Creates `roles` table** with portal access permissions
2. **Adds `role_id` column** to `employees`, `managers`, and `admins` tables
3. **Sets default roles** for existing users:
   - Employees → Role ID 1 (Employee)
   - Managers → Role ID 2 (Manager)
   - HR/Admins → Role ID 3 (HR)

## How to Run the Migration

### Option 1: Using phpMyAdmin or MySQL Client

1. Open phpMyAdmin or your MySQL client
2. Select your database (`payroll_db`)
3. Go to the SQL tab
4. Copy and paste the contents of `migrate_roles_system.sql`
5. Click "Go" or execute the SQL

### Option 2: Using Command Line

```bash
mysql -u your_username -p payroll_db < app/database/migrate_roles_system.sql
```

## Default Roles Created

| Role ID | Role Name | Employee Portal | Manager Portal | HR Portal | Owner Portal |
|---------|-----------|----------------|----------------|-----------|--------------|
| 1 | Employee | ✅ | ❌ | ❌ | ❌ |
| 2 | Manager | ✅ | ✅ | ❌ | ❌ |
| 3 | HR | ✅ | ❌ | ✅ | ❌ |
| 4 | HR Manager | ✅ | ✅ | ✅ | ❌ |
| 5 | Supervisor | ✅ | ✅ | ❌ | ❌ |
| 6 | Owner | ✅ | ✅ | ✅ | ✅ |

## What Happens After Migration

1. **All existing employees** will have `role_id = 1` (Employee role)
2. **All existing managers** will have `role_id = 2` (Manager role) - **CAN ACCESS EMPLOYEE PORTAL**
3. **All existing HR/admins** will have `role_id = 3` (HR role) - **CAN ACCESS EMPLOYEE PORTAL**

## Testing After Migration

1. **Test Employee Login:**
   - Log in as an employee through the employee portal
   - Should work as before

2. **Test Manager Login:**
   - Log in as a manager using the **employee portal** login form
   - Manager should be able to access employee portal features
   - Manager can also access manager portal as before

3. **Test HR Login:**
   - Log in as HR using the **employee portal** login form
   - HR should be able to access employee portal features
   - HR can also access HR portal as before

## Important Notes

- **Backup your database** before running the migration
- The migration is **non-destructive** - it only adds columns and creates a new table
- Existing functionality will continue to work
- Users need to **log out and log back in** for role changes to take effect in their session

## Troubleshooting

### Error: "Table 'roles' already exists"
- This means the roles table was already created. The migration will skip creating it.

### Error: "Column 'role_id' already exists"
- This means the column was already added. The migration will skip adding it.

### Users cannot access employee portal after migration
- Make sure they have the correct `role_id` set in their table
- Check that the role has `can_access_employee_portal = 1`
- Users must log out and log back in for changes to take effect

## Rollback (If Needed)

If you need to rollback the migration:

```sql
-- Remove foreign key constraints
ALTER TABLE `employees` DROP FOREIGN KEY `fk_employee_role`;
ALTER TABLE `managers` DROP FOREIGN KEY `fk_manager_role`;
ALTER TABLE `admins` DROP FOREIGN KEY `fk_admin_role`;

-- Remove columns
ALTER TABLE `employees` DROP COLUMN `role_id`;
ALTER TABLE `managers` DROP COLUMN `role_id`;
ALTER TABLE `admins` DROP COLUMN `role_id`;

-- Drop roles table (optional)
DROP TABLE IF EXISTS `roles`;
```

## Support

If you encounter any issues during migration, check:
1. Database user has ALTER TABLE permissions
2. Database user has CREATE TABLE permissions
3. No foreign key constraints are blocking the changes
4. All tables exist before running the migration

