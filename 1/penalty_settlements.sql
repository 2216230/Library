-- Penalty Settlements Table
-- Stores all settlement records with fine details and adjustments

CREATE TABLE IF NOT EXISTS penalty_settlements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    borrower_id INT,
    book_id INT,
    borrower_name VARCHAR(255),
    book_title VARCHAR(255),
    days_overdue INT NOT NULL DEFAULT 0,
    due_date DATE,
    return_date DATE,
    
    -- Fine Calculation Details
    fine_per_day DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    chargeable_days INT NOT NULL DEFAULT 0,
    calculated_fine DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    
    -- Adjustment Details
    adjustment_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    adjustment_reason VARCHAR(100),
    -- Reasons: discount, waived, exclusion (MINUS)
    --         lost_book, partial_return (PLUS)
    adjustment_details TEXT,
    
    -- Final Amount
    total_payable DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    
    -- Return Status
    return_status VARCHAR(50),
    -- Values: good, damaged, repair (lost)
    
    -- Settlement Info
    settled_by INT,
    settled_by_name VARCHAR(255),
    settled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Status
    status VARCHAR(50) DEFAULT 'settled',
    -- Values: settled, partially_paid, unpaid (if needed in future)
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for faster queries
    KEY idx_transaction_id (transaction_id),
    KEY idx_borrower_id (borrower_id),
    KEY idx_book_id (book_id),
    KEY idx_settled_at (settled_at),
    KEY idx_status (status),
    FOREIGN KEY (transaction_id) REFERENCES borrow_transactions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for date range queries
CREATE INDEX idx_settled_date_range ON penalty_settlements(settled_at);
