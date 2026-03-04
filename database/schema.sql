-- Users table with roles
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('visitor', 'employee', 'admin') DEFAULT 'visitor',
    first_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add default admin user (password: Admin123!)
INSERT INTO users (email, password_hash, role) VALUES
('admin@haarlemfestival.nl', '$2y$12$48/mAMTpsHZ0vcFitlPY9O3Gk.nnYHt9ZNA/ULGtzKYMdrUZQMJUW', 'admin')
ON DUPLICATE KEY UPDATE password_hash='$2y$12$48/mAMTpsHZ0vcFitlPY9O3Gk.nnYHt9ZNA/ULGtzKYMdrUZQMJUW';

-- Events table
CREATE TABLE IF NOT EXISTS events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  category VARCHAR(100) NOT NULL,
  event_date DATE NOT NULL,
  start_time TIME NULL,
  end_time TIME NULL,
  venue VARCHAR(255) NULL,
  price DECIMAL(10,2) NULL,
  image_path VARCHAR(255) NULL,
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
