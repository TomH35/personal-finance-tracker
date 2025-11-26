-- ========================================
--  DATABASE: finance_tracker_db
-- ========================================
CREATE DATABASE IF NOT EXISTS finance_tracker_db;
USE finance_tracker_db;

-- ========================================
--  TABLE: users
-- ========================================
-- Each record represents one registered user in the system.
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,          -- User ID
    username VARCHAR(50) UNIQUE NOT NULL,            -- Username
    email VARCHAR(255) NOT NULL,		     -- Email
    password_hash VARCHAR(255) NOT NULL,             -- Hashed password
    role ENUM('admin', 'user') DEFAULT 'user',       -- User role
    currency VARCHAR(3) DEFAULT 'USD',               -- User's preferred currency (ISO 4217 code)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,    -- Creation time
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP   -- Last update time
);

-- ========================================
--  TABLE: categories
-- ========================================
-- Each record represents one expense or income category.
-- Predefined: user_id = NULL; Custom: user_id = user's ID.
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,       -- Category ID
    user_id INT NULL,                                 -- Linked user (NULL = predefined)
    name VARCHAR(100) NOT NULL,                       -- Category name
    type ENUM('expense', 'income') DEFAULT 'expense', -- Category type
    is_predefined BOOLEAN DEFAULT FALSE,              -- Predefined flag
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,    -- Creation time

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ========================================
--  TABLE: transactions
-- ========================================
-- Each record represents one financial transaction (expense or income)
-- created by a specific user and optionally linked to a category.
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,   -- Transaction ID
    user_id INT NOT NULL,                            -- Linked user
    category_id INT NULL,                            -- Linked category (nullable)
    amount DECIMAL(10,2) NOT NULL,                   -- Transaction amount
    type ENUM('expense', 'income') NOT NULL,         -- Transaction type
    note VARCHAR(255) NULL,                          -- Optional note
    date DATE NOT NULL,                              -- Transaction date

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

-- ========================================
--  TABLE: spending_limits
-- ========================================
-- Each record represents one user's monthly spending limit
-- including thresholds for warning and critical notifications.
CREATE TABLE spending_limits (
    limit_id INT AUTO_INCREMENT PRIMARY KEY,         -- Limit ID
    user_id INT NOT NULL,                            -- Linked user
    warning_limit DECIMAL(10,2) NOT NULL,            -- Warning threshold
    critical_limit DECIMAL(10,2) NOT NULL,           -- Critical threshold
    enabled TINYINT(1) NOT NULL DEFAULT 1,           -- Whether limit is active

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ========================================
--  TABLE: notifications
-- ========================================
-- Each record represents one notification for a user,
-- optionally linked to a spending limit (warning or critical).
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,   			-- Notification ID
    user_id INT NOT NULL,                             			-- Linked user
    limit_id INT NULL,                                			-- Linked limit (optional)
    type ENUM('warning', 'critical', 'info') DEFAULT 'info',  	-- Notification type
    message VARCHAR(255) NOT NULL,                    			-- Message text
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,    			-- Creation time
    is_read BOOLEAN DEFAULT FALSE,                    			-- Read flag

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (limit_id) REFERENCES spending_limits(limit_id) ON DELETE SET NULL
);

-- ========================================
--  TABLE: rate_limits
-- ========================================
-- Each record represents one login or registration attempt,
-- including the IP address, optionally linked user, endpoint, 
-- timestamp of the attempt, and temporary ban expiration if any.
CREATE TABLE rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,            	-- Attempt ID
    ip VARCHAR(50) NOT NULL,                      	-- IP address of the user
    user_id INT NULL,                             	-- Linked user (if known)
    endpoint VARCHAR(100) NOT NULL,               	-- API endpoint (login/register)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Attempt timestamp
    blocked_until TIMESTAMP NULL,                  	-- Ban expiry timestamp (if blocked)
    require_captcha TINYINT(1) DEFAULT 0          -- Whether captcha is required for this attempt
);

