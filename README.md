# Fitness CRM & Subscription Portal

A comprehensive Gym Management System designed to automate member subscriptions, trainer scheduling, and billing operations. This full-stack solution centralizes data to reduce manual errors and improve the operational efficiency of fitness centers.

## 📖 Overview
The **Fitness CRM & Subscription Portal** addresses the need for operational automation in gyms. It replaces fragmented records and manual billing with a centralized platform that offers specific portals for Administrators, Staff, and Members.

**Key capabilities include:**
- Automated subscription management and recurring billing.
- Real-time attendance tracking for members and staff.
- Role-based access control (Admin, Staff, Member).
- Responsive design for access on desktops and tablets.

## 🚀 Features

### 👤 Member Portal (Self-Service)
- **Online Registration:** Fast sign-up with digital waivers and photo uploads.
- **Subscription Management:** View active plans, renewal dates, and payment history.
- **Dashboard:** Track daily attendance and view assigned trainer notes.
- **Transparency:** Access class bookings and personal progress reports.

### 🛠 Admin Dashboard
- **Real-Time KPIs:** View active members, daily earnings, and low-stock alerts.
- **User Management:** Manage member profiles, membership tiers, and staff roles.
- **Financials:** Generate revenue reports and manage invoices.
- **Resource Management:** Assign trainers to members and manage equipment inventory.

### 📋 Staff Panel
- **Scheduling:** Book appointments and manage trainer availability.
- **Attendance:** Automated check-in/check-out for members.
- **Member Support:** Access member profiles to assist with inquiries.

## 💻 Tech Stack

* **Frontend:** HTML5, CSS3, JavaScript, Bootstrap (Responsive Design)
* **Backend:** PHP (Server-side logic & APIs)
* **Database:** MySQL (Relational schema for members, payments, schedules)
* **Local Development:** XAMPP / MAMP
* **Security:** Session management, password hashing, and input validation.

## ⚙️ Installation & Setup

1.  **Clone the Repository**
    ```bash
    git clone [https://github.com/your-username/fitness-crm-portal.git](https://github.com/your-username/fitness-crm-portal.git)
    ```

2.  **Database Setup**
    * Open **phpMyAdmin** (or your preferred SQL tool).
    * Create a new database named `gym_db`.
    * Import the `database.sql` file provided in the `sql/` folder.

3.  **Configure Backend**
    * Navigate to the `config` folder.
    * Update `db_connection.php` with your database credentials:
        ```php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "gym_db";
        ```

4.  **Run the Project**
    * Place the project folder in your `htdocs` (XAMPP) or `www` (WAMP) directory.
    * Start **Apache** and **MySQL** servers.
    * Open your browser and visit: `http://localhost/fitness-crm-portal`

## 🔮 Future Scope
* **Mobile App:** Native application development for easier booking and push notifications.
* **AI Analytics:** Integration of ML models for member churn prediction and personalized workout recommendations.
* **Biometrics:** Integration with fingerprint or face scanners for automated entry.

## 👥 Contributors
* **[Krish Chapadia]()** - *Backend Developer* 

## 📄 License
This project is open-source and available under the [MIT License](LICENSE).
