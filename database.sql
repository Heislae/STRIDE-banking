-- START [SQL QUERY NUMBER 1]
-- database.sql
CREATE DATABASE vulnerable_app;
USE vulnerable_app;
-- END [SQL QUERY NUMBER 1]

-- START [SQL QUERY NUMBER 2]
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    full_name VARCHAR(100),
    address TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description VARCHAR(255),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
-- END [SQL QUERY NUMBER 2]

-- START [SQL QUERY NUMBER 3]
-- Insert admin user (password: Admin@123)
INSERT INTO users (username, password, email, role, full_name, address, phone)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'admin', 'Admin User', '123 Admin St', '555-1234');

-- Insert regular user (password: User@123)
INSERT INTO users (username, password, email, role, full_name, address, phone)
VALUES ('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user1@example.com', 'user', 'Regular User', '456 User Ave', '555-5678');

--Insert regular user (cust1 cust1)
INSERT INTO users (username, password, email, role, full_name, address, phone)
VALUES ('user1', '$2y$10$oEeiWA7hLxJeIF4xmgz4Y.MXUw5d2oQXmsEWD2pH.f.2CvT8ZHfkC', 'user1@example.com', 'user', 'Regular User', '456 User Ave', '555-5678');
-- END [SQL QUERY NUMBER 3]


-- START [SQL QUERY NUMBER 4]
-- Sample transactions
INSERT INTO transactions (user_id, amount, description)
VALUES (1, 1000.00, 'Initial deposit'), (2, 500.00, 'Initial deposit');

-- Add these to your database.sql file or run them directly in your MySQL client

-- Transaction 1: Salary deposit
INSERT INTO transactions (user_id, amount, description)
VALUES (
    (SELECT id FROM users WHERE username = 'cust1'), 
    2500.00, 
    'Monthly salary deposit'
);

-- Transaction 2: Grocery store purchase
INSERT INTO transactions (user_id, amount, description)
VALUES (
    (SELECT id FROM users WHERE username = 'cust1'), 
    -125.75, 
    'Grocery purchase at SuperMart'
);

-- Transaction 3: Utility bill payment
INSERT INTO transactions (user_id, amount, description)
VALUES (
    (SELECT id FROM users WHERE username = 'cust1'), 
    -85.50, 
    'Electricity bill payment'
);

-- Transaction 4: Online shopping
INSERT INTO transactions (user_id, amount, description)
VALUES (
    (SELECT id FROM users WHERE username = 'cust1'), 
    -65.99, 
    'Amazon purchase - electronics'
);

-- Transaction 5: Restaurant visit
INSERT INTO transactions (user_id, amount, description)
VALUES (
    (SELECT id FROM users WHERE username = 'cust1'), 
    -42.30, 
    'Dinner at Italian Bistro'
);

-- Transaction 6: Freelance payment
INSERT INTO transactions (user_id, amount, description)
VALUES (
    (SELECT id FROM users WHERE username = 'cust1'), 
    750.00, 
    'Freelance web development payment'
);

-- Transaction 7: ATM withdrawal
INSERT INTO transactions (user_id, amount, description)
VALUES (
    (SELECT id FROM users WHERE username = 'cust1'), 
    -200.00, 
    'ATM cash withdrawal'
);

-- Transaction 8: Gym membership
INSERT INTO transactions (user_id, amount, description)
VALUES (
    (SELECT id FROM users WHERE username = 'cust1'), 
    -35.00, 
    'Monthly gym membership'
);

-- END [SQL QUERY NUMBER 4]

-- START [SQL QUERY NUMBER 5]
-- Add columns for password reset functionality
ALTER TABLE users 
ADD COLUMN reset_token VARCHAR(64) NULL,
ADD COLUMN reset_expires INT NULL;

-- END [SQL QUERY NUMBER 5]