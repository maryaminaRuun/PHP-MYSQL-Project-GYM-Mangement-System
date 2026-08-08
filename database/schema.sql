CREATE DATABASE IF NOT EXISTS gym_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gym_management;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  FullName VARCHAR(120) NOT NULL,
  Username VARCHAR(80) NOT NULL UNIQUE,
  Email VARCHAR(190) NOT NULL UNIQUE,
  Password VARCHAR(255) NOT NULL,
  Role ENUM('Admin','Manager','Cashier','Trainer') NOT NULL DEFAULT 'Cashier',
  Status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS memberships (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  MembershipType VARCHAR(100) NOT NULL,
  Price DECIMAL(12,2) NOT NULL,
  Duration INT UNSIGNED NOT NULL COMMENT 'Duration in days',
  status ENUM('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS trainers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  FullName VARCHAR(120) NOT NULL, Gender VARCHAR(20), Phone VARCHAR(30),
  Email VARCHAR(190), Address VARCHAR(255), HireDate DATE,
  Status ENUM('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS class (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  class_name VARCHAR(120) NOT NULL, description TEXT,
  trainer_id INT UNSIGNED NULL, capacity INT UNSIGNED DEFAULT 20,
  CONSTRAINT fk_class_trainer FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS schedule (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  class_id INT UNSIGNED NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL,
  location VARCHAR(150),
  CONSTRAINT fk_schedule_class FOREIGN KEY (class_id) REFERENCES class(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS members (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  FullName VARCHAR(120) NOT NULL, DateOfBirth DATE, Gender VARCHAR(20), Phone VARCHAR(30),
  Email VARCHAR(190), Address VARCHAR(255), MemberWeight DECIMAL(6,2),
  MembershipID INT UNSIGNED NULL, schedule_id INT UNSIGNED NULL,
  start_date DATE NULL, end_date DATE NULL,
  Status ENUM('Active','Inactive','Expired','Frozen') DEFAULT 'Active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_member_membership FOREIGN KEY (MembershipID) REFERENCES memberships(id) ON DELETE SET NULL,
  CONSTRAINT fk_member_schedule FOREIGN KEY (schedule_id) REFERENCES schedule(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS equipments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  EquipmentName VARCHAR(150) NOT NULL, PurchaseCost DECIMAL(12,2) DEFAULT 0,
  Quantity INT UNSIGNED DEFAULT 1, PurchaseDate DATE, condition_status VARCHAR(40) DEFAULT 'Good',
  next_maintenance_date DATE NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS banks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL, account_num VARCHAR(100) NOT NULL UNIQUE,
  balance DECIMAL(14,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS charges (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  member_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NULL, Price DECIMAL(12,2) NOT NULL,
  date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, billing_month CHAR(7) NOT NULL,
  remarks VARCHAR(255),
  status ENUM('Unpaid','Partially Paid','Paid','Void') DEFAULT 'Unpaid',
  CONSTRAINT fk_charge_member FOREIGN KEY (member_id) REFERENCES members(id),
  CONSTRAINT fk_charge_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  UNIQUE KEY uq_member_month (member_id, billing_month)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  receipt_no VARCHAR(30) NOT NULL UNIQUE, member_id INT UNSIGNED NOT NULL,
  charge_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NULL, amount DECIMAL(12,2) NOT NULL,
  bank_id INT UNSIGNED NOT NULL, PaymentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  notes VARCHAR(255),
  CONSTRAINT fk_payment_member FOREIGN KEY (member_id) REFERENCES members(id),
  CONSTRAINT fk_payment_charge FOREIGN KEY (charge_id) REFERENCES charges(id),
  CONSTRAINT fk_payment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_payment_bank FOREIGN KEY (bank_id) REFERENCES banks(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS expense_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL UNIQUE,
  description VARCHAR(255), status ENUM('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS expenses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, expense_no VARCHAR(30) NOT NULL UNIQUE,
  category_id INT UNSIGNED NOT NULL, bank_id INT UNSIGNED NOT NULL, amount DECIMAL(12,2) NOT NULL,
  expense_date DATE NOT NULL, payee VARCHAR(150) NOT NULL, description VARCHAR(255),
  reference_no VARCHAR(100), user_id INT UNSIGNED NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_expense_category FOREIGN KEY (category_id) REFERENCES expense_categories(id),
  CONSTRAINT fk_expense_bank FOREIGN KEY (bank_id) REFERENCES banks(id),
  CONSTRAINT fk_expense_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, member_id INT UNSIGNED NOT NULL,
  check_in DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, check_out DATETIME NULL,
  notes VARCHAR(255), recorded_by INT UNSIGNED NULL,
  CONSTRAINT fk_att_member FOREIGN KEY (member_id) REFERENCES members(id),
  CONSTRAINT fk_att_user FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_attendance_date (check_in), INDEX idx_attendance_member (member_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NULL,
  action VARCHAR(80) NOT NULL, entity_type VARCHAR(80) NOT NULL, entity_id INT UNSIGNED NULL,
  details JSON NULL, ip_address VARCHAR(45), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_entity (entity_type, entity_id), INDEX idx_audit_date (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_resets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, email VARCHAR(190) NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE, expires_at DATETIME NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS accounts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE, name VARCHAR(120) NOT NULL,
  type ENUM('Asset','Liability','Equity','Revenue','Expense') NOT NULL,
  normal_balance ENUM('Debit','Credit') NOT NULL,
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS journal_entries (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entry_no VARCHAR(30) NOT NULL UNIQUE, entry_date DATE NOT NULL,
  description VARCHAR(255) NOT NULL, source_type VARCHAR(40), source_id BIGINT UNSIGNED,
  status ENUM('Posted','Void') NOT NULL DEFAULT 'Posted', user_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_journal_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_journal_date (entry_date), INDEX idx_journal_source (source_type, source_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS journal_lines (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, journal_entry_id BIGINT UNSIGNED NOT NULL,
  account_id INT UNSIGNED NOT NULL, debit DECIMAL(14,2) NOT NULL DEFAULT 0,
  credit DECIMAL(14,2) NOT NULL DEFAULT 0, memo VARCHAR(255),
  CONSTRAINT fk_line_entry FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id) ON DELETE CASCADE,
  CONSTRAINT fk_line_account FOREIGN KEY (account_id) REFERENCES accounts(id),
  INDEX idx_line_account (account_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payroll (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, payroll_no VARCHAR(30) NOT NULL UNIQUE,
  trainer_id INT UNSIGNED NULL, employee_name VARCHAR(150) NOT NULL,
  period_start DATE NOT NULL, period_end DATE NOT NULL, basic_salary DECIMAL(12,2) NOT NULL,
  allowance DECIMAL(12,2) NOT NULL DEFAULT 0, deduction DECIMAL(12,2) NOT NULL DEFAULT 0,
  net_pay DECIMAL(12,2) NOT NULL, bank_id INT UNSIGNED NOT NULL,
  payment_date DATE NOT NULL, status ENUM('Paid','Void') DEFAULT 'Paid', user_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payroll_trainer FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE SET NULL,
  CONSTRAINT fk_payroll_bank FOREIGN KEY (bank_id) REFERENCES banks(id),
  CONSTRAINT fk_payroll_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO expense_categories (id, name) VALUES
(1,'Rent'),(2,'Utilities'),(3,'Salaries'),(4,'Equipment'),(5,'Maintenance'),(6,'Marketing'),(7,'Other');
INSERT IGNORE INTO memberships (id, MembershipType, Price, Duration) VALUES
(1,'Monthly',30,30),(2,'Quarterly',80,90),(3,'Annual',280,365);
INSERT IGNORE INTO accounts (code,name,type,normal_balance) VALUES
('1000','Cash and Bank','Asset','Debit'),('1100','Membership Receivable','Asset','Debit'),
('1500','Gym Equipment','Asset','Debit'),('2000','Accounts Payable','Liability','Credit'),
('3000','Owner Equity','Equity','Credit'),('4000','Membership Revenue','Revenue','Credit'),
('4100','Other Revenue','Revenue','Credit'),('5000','Operating Expense','Expense','Debit'),
('5100','Salary Expense','Expense','Debit'),('5200','Utilities Expense','Expense','Debit');
