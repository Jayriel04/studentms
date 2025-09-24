-- Migration: Create tables for achievements, skills and links

CREATE TABLE IF NOT EXISTS `skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(191) NOT NULL UNIQUE,
  `category` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `student_achievements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `StuID` VARCHAR(100) NOT NULL,
  `level` VARCHAR(50) NOT NULL,
  `category` VARCHAR(50) DEFAULT NULL,
  `proof_image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
  `points` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`StuID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `student_achievement_skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `achievement_id` INT NOT NULL,
  `skill_id` INT NOT NULL,
  FOREIGN KEY (`achievement_id`) REFERENCES `student_achievements`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: table to store points assigned when admin approves
CREATE TABLE IF NOT EXISTS `achievement_approvals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `achievement_id` INT NOT NULL,
  `approved_by` VARCHAR(100) DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `notes` TEXT,
  FOREIGN KEY (`achievement_id`) REFERENCES `student_achievements`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Setup notes:
-- 1. Run this SQL in your `studentmsdb` database (phpMyAdmin or MySQL CLI).
-- 2. Ensure the webserver user can write to `admin/images/achievements/` directory. If missing, create it:
--    mkdir -p admin/images/achievements && chown -R www-data:www-data admin/images/achievements
-- 3. To list achievements for staff/admin, visit `staff/validate-achievements.php` and approve/reject.
-- 4. To search students by skill and sort by points, use the skill filter in `staff/manage-students.php`.
