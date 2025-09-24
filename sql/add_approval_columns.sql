-- Migration: add approval audit columns to student_achievements
ALTER TABLE student_achievements
  ADD COLUMN IF NOT EXISTS approved_by INT NULL AFTER status,
  ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL AFTER approved_by;

-- Optionally create an index for faster lookups by approver
CREATE INDEX IF NOT EXISTS idx_student_achievements_approved_by ON student_achievements(approved_by);
