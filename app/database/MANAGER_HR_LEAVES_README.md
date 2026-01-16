# Manager and HR Leave Application System

## Overview
This system extends the leave application functionality to support Manager and HR leave applications with the following approval workflow:
- **Employee Leave**: Employee → Manager (existing)
- **Manager Leave**: Manager → HR (new)
- **HR Leave**: HR → Owner (new)

## Database Migration

### Step 1: Run the Migration Script

**Note:** The migration has already been completed. The SQL file (`migrate_manager_hr_leaves.sql`) is provided for reference only.

If you need to run the migration manually:

#### Using phpMyAdmin or MySQL Client
1. Open `app/database/migrate_manager_hr_leaves.sql`
2. **Important:** The SQL file uses `IF NOT EXISTS` syntax which MySQL doesn't support for `ALTER TABLE ADD COLUMN`. You may need to:
   - Remove `IF NOT EXISTS` from `ADD COLUMN` statements, or
   - Check if columns exist before running the statements
3. Execute the SQL in phpMyAdmin or your MySQL client

**Note:** The SQL file also contains `PREPARE/EXECUTE` blocks for foreign key constraints. These work in MySQL command line but may have issues with some GUI tools.

### What the Migration Does

1. **Updates `leaves` table**:
   - Adds `applicant_manager_id` (for Manager applicants)
   - Adds `applicant_hr_id` (for HR applicants)
   - Adds `applicant_type` enum ('employee', 'manager', 'hr')
   - Adds `approver_hr_id` (HR approves Manager leaves)
   - Adds `approver_owner_id` (Owner approves HR leaves)
   - Adds `approver_type` enum ('manager', 'hr', 'owner')
   - Adds `approver_name` (cached approver name for display)
   - Adds indexes and foreign key constraints

2. **Updates `leave_credits` table**:
   - Adds `manager_id` (for Manager leave credits)
   - Adds `hr_id` (for HR leave credits)
   - Adds `user_type` enum ('employee', 'manager', 'hr')
   - Adds indexes and foreign key constraints

## Features Implemented

### 1. User Leave Application (`user_leave.view.php` & `user_leave-api.php`)
- ✅ Supports Employee, Manager, and HR leave applications
- ✅ Auto-initializes leave credits for all user types
- ✅ Displays approver information for approved/rejected leaves
- ✅ Validates leave credits before submission
- ✅ Handles medical certificates and reasons based on leave type

### 2. HR Manager Leave Approval (`hr_manager_leave_approval-api.php`)
- ✅ Fetches pending Manager leave applications
- ✅ Approves/Rejects Manager leaves
- ✅ Deducts leave credits from Manager's account when approved
- ✅ Stores approver information (HR name and ID)

### 3. Owner HR Leave Approval (`owner_hr_leave_approval-api.php`)
- ✅ Fetches pending HR leave applications
- ✅ Approves/Rejects HR leaves
- ✅ Deducts leave credits from HR's account when approved
- ✅ Stores approver information (Owner name and ID)

### 4. Owner Leave Management View (`owner_leavemanagement.view.php`)
- ✅ Displays HR leave applications in a table
- ✅ Allows Owner to approve/reject HR leaves
- ✅ Shows leave details (reason, medical certificate)
- ✅ Displays approver information
- ✅ Search functionality

## API Endpoints

### User Leave API (`user_leave-api.php`)
- **GET**: Fetch user's leave applications (supports Employee, Manager, HR)
- **POST**: Submit new leave application
- **DELETE**: Delete pending leave application

### HR Manager Leave Approval API (`hr_manager_leave_approval-api.php`)
- **GET**: Fetch pending Manager leave applications
- **POST**: Approve or reject Manager leave
  ```json
  {
    "action": "approve" | "reject",
    "leave_id": 123,
    "remarks": "Optional rejection reason"
  }
  ```

### Owner HR Leave Approval API (`owner_hr_leave_approval-api.php`)
- **GET**: Fetch pending HR leave applications
- **POST**: Approve or reject HR leave
  ```json
  {
    "action": "approve" | "reject",
    "leave_id": 123,
    "remarks": "Optional rejection reason"
  }
  ```

## Approval Workflow

### Employee Leave
1. Employee submits leave application
2. Manager reviews and approves/rejects
3. If approved, leave credits deducted from Employee's account

### Manager Leave
1. Manager submits leave application
2. HR reviews and approves/rejects
3. If approved, leave credits deducted from Manager's account

### HR Leave
1. HR submits leave application
2. Owner reviews and approves/rejects
3. If approved, leave credits deducted from HR's account

## Session Variables Required

### For Employee
- `employee_id` or `employee_no`

### For Manager
- `manager_id`

### For HR/Admin
- `SESSION_USER_ID`

### For Owner
- `owner_id`

## Testing Checklist

- [ ] Run database migration successfully
- [ ] Employee can apply for leave (existing functionality)
- [ ] Manager can apply for leave
- [ ] HR can apply for leave
- [ ] Manager can approve Employee leaves (existing functionality)
- [ ] HR can approve Manager leaves
- [ ] Owner can approve HR leaves
- [ ] Leave credits are correctly deducted for all user types
- [ ] Approver information is displayed correctly
- [ ] Search functionality works in Owner leave management view

## Notes

- The migration script includes checks to prevent duplicate columns/indexes/constraints
- Leave credits are auto-initialized when a user first accesses the leave page
- The system maintains backward compatibility with existing Employee leave applications
- All approver information is cached in the `approver_name` field for quick display

