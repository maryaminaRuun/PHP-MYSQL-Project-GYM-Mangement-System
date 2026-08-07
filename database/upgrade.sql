-- Upgrade an existing Gym Management database without deleting its records.
-- BACK UP THE DATABASE BEFORE RUNNING THIS FILE. Requires MySQL 8.0.29+.
USE gymmanagementsystem;

ALTER TABLE users MODIFY Password VARCHAR(255) NOT NULL;
ALTER TABLE members ADD COLUMN IF NOT EXISTS start_date DATE NULL, ADD COLUMN IF NOT EXISTS end_date DATE NULL, ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE equipments ADD COLUMN IF NOT EXISTS condition_status VARCHAR(40) DEFAULT 'Good', ADD COLUMN IF NOT EXISTS next_maintenance_date DATE NULL;
ALTER TABLE charges ADD COLUMN IF NOT EXISTS billing_month CHAR(7) NULL;
UPDATE charges SET billing_month=DATE_FORMAT(date,'%Y-%m') WHERE billing_month IS NULL OR billing_month='';
ALTER TABLE charges MODIFY billing_month CHAR(7) NOT NULL;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS receipt_no VARCHAR(30) NULL, ADD COLUMN IF NOT EXISTS notes VARCHAR(255) NULL;
UPDATE payments SET receipt_no=CONCAT('LEGACY-',LPAD(id,8,'0')) WHERE receipt_no IS NULL OR receipt_no='';
ALTER TABLE payments MODIFY receipt_no VARCHAR(30) NOT NULL;

CREATE TABLE IF NOT EXISTS expense_categories (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL UNIQUE,description VARCHAR(255),status ENUM('Active','Inactive') DEFAULT 'Active') ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS expenses (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,expense_no VARCHAR(30) NOT NULL UNIQUE,category_id INT UNSIGNED NOT NULL,bank_id INT UNSIGNED NOT NULL,amount DECIMAL(12,2) NOT NULL,expense_date DATE NOT NULL,payee VARCHAR(150) NOT NULL,description VARCHAR(255),reference_no VARCHAR(100),user_id INT UNSIGNED NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(category_id),INDEX(bank_id)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS attendance (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,member_id INT UNSIGNED NOT NULL,check_in DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,check_out DATETIME NULL,notes VARCHAR(255),recorded_by INT UNSIGNED NULL,INDEX(check_in),INDEX(member_id)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS audit_logs (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id INT UNSIGNED NULL,action VARCHAR(80) NOT NULL,entity_type VARCHAR(80) NOT NULL,entity_id INT UNSIGNED NULL,details JSON NULL,ip_address VARCHAR(45),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(entity_type,entity_id),INDEX(created_at)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS accounts (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,code VARCHAR(20) NOT NULL UNIQUE,name VARCHAR(120) NOT NULL,type ENUM('Asset','Liability','Equity','Revenue','Expense') NOT NULL,normal_balance ENUM('Debit','Credit') NOT NULL,status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS journal_entries (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,entry_no VARCHAR(30) NOT NULL UNIQUE,entry_date DATE NOT NULL,description VARCHAR(255) NOT NULL,source_type VARCHAR(40),source_id BIGINT UNSIGNED,status ENUM('Posted','Void') NOT NULL DEFAULT 'Posted',user_id INT UNSIGNED NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(entry_date),INDEX(source_type,source_id)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS journal_lines (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,journal_entry_id BIGINT UNSIGNED NOT NULL,account_id INT UNSIGNED NOT NULL,debit DECIMAL(14,2) NOT NULL DEFAULT 0,credit DECIMAL(14,2) NOT NULL DEFAULT 0,memo VARCHAR(255),FOREIGN KEY(journal_entry_id) REFERENCES journal_entries(id) ON DELETE CASCADE,FOREIGN KEY(account_id) REFERENCES accounts(id),INDEX(account_id)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS payroll (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,payroll_no VARCHAR(30) NOT NULL UNIQUE,trainer_id INT UNSIGNED NULL,employee_name VARCHAR(150) NOT NULL,period_start DATE NOT NULL,period_end DATE NOT NULL,basic_salary DECIMAL(12,2) NOT NULL,allowance DECIMAL(12,2) NOT NULL DEFAULT 0,deduction DECIMAL(12,2) NOT NULL DEFAULT 0,net_pay DECIMAL(12,2) NOT NULL,bank_id INT UNSIGNED NOT NULL,payment_date DATE NOT NULL,status ENUM('Paid','Void') DEFAULT 'Paid',user_id INT UNSIGNED NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(trainer_id),INDEX(bank_id)) ENGINE=InnoDB;

INSERT IGNORE INTO expense_categories(id,name) VALUES(1,'Rent'),(2,'Utilities'),(3,'Salaries'),(4,'Equipment'),(5,'Maintenance'),(6,'Marketing'),(7,'Other');
INSERT IGNORE INTO accounts(code,name,type,normal_balance) VALUES
('1000','Cash and Bank','Asset','Debit'),('1100','Membership Receivable','Asset','Debit'),('1500','Gym Equipment','Asset','Debit'),('2000','Accounts Payable','Liability','Credit'),('3000','Owner Equity','Equity','Credit'),('4000','Membership Revenue','Revenue','Credit'),('4100','Other Revenue','Revenue','Credit'),('5000','Operating Expense','Expense','Debit'),('5100','Salary Expense','Expense','Debit'),('5200','Utilities Expense','Expense','Debit');
