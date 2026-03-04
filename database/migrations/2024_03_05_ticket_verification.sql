-- Day 6: Ticket Verification & QR Scanning
-- Add columns for ticket status tracking

ALTER TABLE order_items ADD COLUMN is_used BOOLEAN DEFAULT FALSE AFTER price_at_purchase;
ALTER TABLE order_items ADD COLUMN used_at TIMESTAMP NULL AFTER is_used;
ALTER TABLE order_items ADD COLUMN checked_by_user_id INT NULL AFTER used_at;
ALTER TABLE order_items ADD FOREIGN KEY (checked_by_user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Add index for quick lookups
CREATE INDEX idx_order_items_is_used ON order_items(is_used);
CREATE INDEX idx_order_items_used_at ON order_items(used_at);

-- Create audit log for ticket scans
CREATE TABLE IF NOT EXISTS ticket_scans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_item_id INT NOT NULL,
    user_id INT,
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(50),
    user_agent TEXT,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_verified_at (verified_at),
    INDEX idx_order_item_id (order_item_id)
);
