-- 1️⃣ Make sure we are using the correct database
USE dermavision_ai;

-- 2️⃣ Remove broken table definition (if MySQL thinks it exists)
DROP TABLE IF EXISTS users;

-- 3️⃣ Force MySQL to forget the old InnoDB tablespace
SET FOREIGN_KEY_CHECKS = 0;

-- 4️⃣ Create users table fresh (correct + compatible)
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
  ROW_FORMAT=DYNAMIC
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- 5️⃣ Re-enable foreign keys
SET FOREIGN_KEY_CHECKS = 1;
