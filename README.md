# Gym Management System

A web-based Gym Management System designed to simplify gym operations such as member registration, trainer management, workout scheduling, and membership payments. Built using PHP, MySQL, Bootstrap 5 for responsive UI, and AJAX for seamless interactivity.

---

## 🔧 Technologies Used

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript, AJAX
- **Backend**: PHP
- **Database**: MySQL
- **Tools**: XAMPP/WAMP, phpMyAdmin

---

## 💡 Features

- ✅ Member Registration & Profile Management  
- ✅ Trainer and Staff Management  
- ✅ Membership Plans with Expiry Tracking  
- ✅ Real-time Member Search with AJAX  
- ✅ Attendance Logging  
- ✅ Admin Dashboard with Metrics & Reports  
- ✅ Responsive UI using Bootstrap 5  
- ✅ Payment Tracking and Invoice Generation  

---

## 📁 Project Structure

## Installation

1. Copy the project into your XAMPP/WAMP web root.
2. Import `database/schema.sql` in phpMyAdmin.
3. Configure the `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` environment variables (see `.env.example`). Defaults work with a standard local XAMPP installation.
4. Visit `setup-admin.php` once to create the first administrator. The setup locks itself immediately afterward.
5. Open `login.php` and sign in.

## Completed business modules

- Members, plans, trainers, classes, schedules and equipment
- Member attendance check-in/check-out
- Recurring membership charges and partial/full payments
- Bank/cash balances with transaction-safe income and expenses
- Expense categories and expense register
- Date-filtered income, expenses, profit, receivables and attendance reports
- Printable/PDF-ready reports, receipts, audit logs and secure password migration

> Before upgrading an existing installation, back up its database. The canonical fresh-install schema is `database/schema.sql`.
