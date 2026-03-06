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

-- Tickets table (Sprint Day 3)
CREATE TABLE IF NOT EXISTS tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  ticket_type VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  quantity_available INT UNSIGNED NOT NULL DEFAULT 0,
  quantity_sold INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tickets_event
    FOREIGN KEY (event_id) REFERENCES events(id)
    ON DELETE CASCADE,
  INDEX idx_tickets_event (event_id),
  UNIQUE KEY uniq_event_ticket_type (event_id, ticket_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table (Sprint Day 4)
CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  customer_email VARCHAR(255) NOT NULL,
  customer_name VARCHAR(255) NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
  stripe_payment_intent_id VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL,
  INDEX idx_orders_user (user_id),
  INDEX idx_orders_status (status),
  INDEX idx_orders_email (customer_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table (Sprint Day 4)
CREATE TABLE IF NOT EXISTS order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  ticket_id INT NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  price_at_purchase DECIMAL(10,2) NOT NULL,
  is_used BOOLEAN NOT NULL DEFAULT FALSE,
  used_at TIMESTAMP NULL,
  checked_by_user_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_order_items_ticket
    FOREIGN KEY (ticket_id) REFERENCES tickets(id)
    ON DELETE RESTRICT,
  CONSTRAINT fk_order_items_checked_by
    FOREIGN KEY (checked_by_user_id) REFERENCES users(id)
    ON DELETE SET NULL,
  INDEX idx_order_items_order (order_id),
  INDEX idx_order_items_ticket (ticket_id),
  INDEX idx_order_items_is_used (is_used),
  INDEX idx_order_items_used_at (used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ticket scan audit log (Sprint Day 6)
CREATE TABLE IF NOT EXISTS ticket_scans (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_item_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL,
  verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(50) NULL,
  user_agent TEXT NULL,
  CONSTRAINT fk_ticket_scans_order_item
    FOREIGN KEY (order_item_id) REFERENCES order_items(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_ticket_scans_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL,
  INDEX idx_ticket_scans_verified_at (verified_at),
  INDEX idx_ticket_scans_order_item_id (order_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
