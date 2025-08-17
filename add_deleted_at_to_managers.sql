-- Add deleted_at column to managers table for soft delete functionality
ALTER TABLE managers ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL;

-- Add index for better performance when querying deleted records
CREATE INDEX IF NOT EXISTS idx_managers_deleted_at ON managers(deleted_at);
