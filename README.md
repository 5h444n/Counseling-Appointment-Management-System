<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js">
  <img src="https://img.shields.io/badge/Vite-7.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="MIT License">
  <br>
  <img src="https://github.com/5h444n/Counseling-Appointment-Management-System/actions/workflows/ci.yml/badge.svg" alt="CI Status">
  <img src="https://github.com/5h444n/Counseling-Appointment-Management-System/actions/workflows/cd.yml/badge.svg" alt="CD Status">
</p>

# 🎓 Counseling Appointment Management System (CAMS)

**CAMS** is a comprehensive web-based appointment scheduling system designed specifically for universities and educational institutions. It streamlines the counseling and advisory appointment process between students and faculty advisors, enabling efficient booking, management, and tracking of counseling sessions.

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Key Features](#-key-features)
- [Tech Stack](#-tech-stack)
- [System Architecture](#-system-architecture)
- [Prerequisites](#-prerequisites)
- [Installation](#-installation)
- [Environment Configuration](#-environment-configuration)
- [Database Setup](#-database-setup)
- [Running the Application](#-running-the-application)
- [User Roles & Permissions](#-user-roles--permissions)
- [Feature Details](#-feature-details)
- [Routes & Endpoints](#-routes--endpoints)
- [Database Schema](#-database-schema)
- [Default Credentials](#-default-credentials)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🎯 Overview

CAMS (Counseling Appointment Management System) is built to address the challenges of managing counseling appointments in academic environments. The system provides:

- **For Students**: Easy discovery of advisors, real-time slot availability, seamless booking with purpose descriptions, and appointment tracking with digital tokens.
- **For Advisors**: Flexible availability management, appointment request handling (approve/decline), and an organized dashboard for pending requests.
- **For Administrators**: User management capabilities and system oversight (coming soon).

The system uses a modern TALL stack (Tailwind CSS, Alpine.js, Laravel, Livewire) architecture ensuring a responsive, fast, and intuitive user experience.

---

## ✨ Key Features

### 🎒 Student Features
| Feature | Description |
|---------|-------------|
| **Advisor Discovery** | Search and filter advisors by name or department |
| **Real-time Availability** | View all available time slots for any advisor |
| **Smart Booking** | Book appointments with purpose description and optional attachments |
| **Digital Tokens** | Receive unique appointment tokens (e.g., CSE-123-A) |
| **Appointment Tracking** | Track appointment status (Pending, Approved, Declined) |
| **Appointment History** | View all past and upcoming appointments |

### 👨‍🏫 Advisor Features
| Feature | Description |
|---------|-------------|
| **Availability Management** | Create flexible time slots with customizable durations (20/30/45/60 min) |
| **Bulk Slot Generation** | Auto-split time ranges into individual slots |
| **Request Dashboard** | View all pending appointment requests |
| **Accept/Decline Actions** | Approve or decline student requests with one click |
| **Slot Protection** | Prevent double-booking with database-level locks |

### 🔐 Authentication & Security
| Feature | Description |
|---------|-------------|
| **Laravel Breeze** | Complete authentication scaffolding |
| **Role-based Access** | Student, Advisor, and Admin roles with middleware protection |
| **Email Verification** | Optional email verification for users |
| **Password Reset** | Secure password reset via email |
| **CSRF Protection** | Built-in Laravel CSRF protection |
| **SQL Injection Prevention** | Eloquent ORM with parameterized queries |

### 🎨 User Experience
| Feature | Description |
|---------|-------------|
| **Responsive Design** | Fully responsive UI works on mobile, tablet, and desktop |
| **Dark Sidebar** | Modern slate-900 sidebar with orange accent colors |
| **Interactive Modals** | Alpine.js powered modals for smooth interactions |
| **Flash Messages** | Success and error notifications |
| **Loading States** | Visual feedback during form submissions |

---

## 🛠 Tech Stack

### Backend
| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 8.2+ | Server-side programming language |
| **Laravel** | 12.x | PHP web application framework |
| **Laravel Breeze** | 2.x | Authentication scaffolding |
| **Laravel Tinker** | 2.x | REPL for Laravel |
| **PHPUnit** | 11.x | Testing framework |

### Frontend
| Technology | Version | Purpose |
|------------|---------|---------|
| **Tailwind CSS** | 4.x | Utility-first CSS framework |
| **Alpine.js** | 3.x | Lightweight JavaScript framework |
| **Vite** | 7.x | Build tool and dev server |
| **Axios** | 1.x | HTTP client for API requests |

### Database
| Technology | Purpose |
|------------|---------|
| **MySQL** | Primary database (recommended) |
| **SQLite** | Alternative for development/testing |
| **PostgreSQL** | Alternative supported database |

### Development Tools
| Tool | Purpose |
|------|---------|
| **Composer** | PHP dependency management |
| **NPM** | JavaScript dependency management |
| **Laravel Pint** | PHP code style fixer |
| **Laravel Sail** | Docker development environment |

---

## 🏗 System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         CAMS Architecture                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐       │
│  │   Student    │    │   Advisor    │    │    Admin     │       │
│  │   Portal     │    │   Portal     │    │   Portal     │       │
│  └──────┬───────┘    └──────┬───────┘    └──────┬───────┘       │
│         │                   │                   │                │
│         └───────────────────┼───────────────────┘                │
│                             │                                    │
│                   ┌─────────▼─────────┐                         │
│                   │   Laravel Router  │                         │
│                   │   + Middleware    │                         │
│                   └─────────┬─────────┘                         │
│                             │                                    │
│         ┌───────────────────┼───────────────────┐               │
│         │                   │                   │                │
│  ┌──────▼──────┐    ┌───────▼───────┐   ┌──────▼──────┐        │
│  │  Student    │    │   Advisor     │   │   Profile   │        │
│  │  Booking    │    │   Slot        │   │   Controller│        │
│  │  Controller │    │   Controller  │   │             │        │
│  └──────┬──────┘    └───────┬───────┘   └──────┬──────┘        │
│         │                   │                   │                │
│         └───────────────────┼───────────────────┘                │
│                             │                                    │
│                   ┌─────────▼─────────┐                         │
│                   │   Eloquent ORM    │                         │
│                   │      Models       │                         │
│                   └─────────┬─────────┘                         │
│                             │                                    │
│                   ┌─────────▼─────────┐                         │
│                   │   MySQL/SQLite    │                         │
│                   │     Database      │                         │
│                   └───────────────────┘                         │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📦 Prerequisites

Before you begin, ensure you have the following installed:

| Requirement | Minimum Version | Check Command |
|-------------|-----------------|---------------|
| **PHP** | 8.2 or higher | `php -v` |
| **Composer** | 2.x | `composer -V` |
| **Node.js** | 18.x or higher | `node -v` |
| **NPM** | 9.x or higher | `npm -v` |
| **MySQL** | 8.x (or SQLite) | `mysql --version` |
| **Git** | 2.x | `git --version` |

### Required PHP Extensions
- PHP PDO Extension
- PHP MySQL Extension (if using MySQL)
- PHP SQLite Extension (if using SQLite)
- PHP Mbstring Extension
- PHP OpenSSL Extension
- PHP Tokenizer Extension
- PHP XML Extension
- PHP Ctype Extension
- PHP JSON Extension
- PHP BCMath Extension

---

## 🚀 Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/5h444n/Counseling-Appointment-Management-System.git
cd Counseling-Appointment-Management-System
```

### Step 2: Navigate to the Application Directory

```bash
cd CAMS
```

### Step 3: Install PHP Dependencies

```bash
composer install
```

### Step 4: Install JavaScript Dependencies

```bash
npm install
```

### Step 5: Create Environment File

```bash
cp .env.example .env
```

### Step 6: Generate Application Key

```bash
php artisan key:generate
```

### Quick Setup (Alternative)

You can also use the composer setup script which automates most of these steps:

```bash
cd CAMS
composer setup
```

This command will:
1. Install Composer dependencies
2. Create `.env` file from `.env.example`
3. Generate application key
4. Run database migrations
5. Install NPM dependencies
6. Build frontend assets

---

## ⚙️ Environment Configuration

Edit the `.env` file in the `CAMS` directory to configure your environment:

### Application Settings

```env
APP_NAME=CAMS
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Database Configuration (MySQL)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cams_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Database Configuration (SQLite Alternative)

```env
DB_CONNECTION=sqlite
# Create the database file: touch database/database.sqlite
```

### Mail Configuration (Optional)

```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@cams.edu"
MAIL_FROM_NAME="${APP_NAME}"
```

### Cache & Session

```env
CACHE_STORE=database
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync
```

---

## 🗄 Database Setup

### Step 1: Create the Database

For MySQL:
```sql
CREATE DATABASE cams_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

For SQLite:
```bash
touch database/database.sqlite
```

### Step 2: Run Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `users` - User accounts with roles
- `departments` - Academic departments
- `appointment_slots` - Advisor availability slots
- `appointments` - Student appointment bookings
- `appointment_documents` - File attachments
- `waitlists` - Slot waitlist entries
- `cache` - Application cache
- `jobs` - Queue jobs

### Step 3: Seed Sample Data (Optional)

```bash
php artisan db:seed
```

This creates:
- 3 Departments (CSE, EEE, BBA)
- 1 Admin user
- 1 Test Student
- 1 Test Advisor
- 10 Random Advisors
- 20 Random Students
- Sample appointment slots

### Step 4: Fresh Migration with Seeding

To reset and reseed the database:

```bash
php artisan migrate:fresh --seed
```

---

## ▶️ Running the Application

### Development Mode

#### Option 1: Using Composer Dev Script (Recommended)

```bash
composer dev
```

This concurrently runs:
- Laravel development server (`php artisan serve`)
- Queue worker (`php artisan queue:listen`)
- Laravel Pail logs (`php artisan pail`)
- Vite dev server (`npm run dev`)

#### Option 2: Manual Start

Terminal 1 - Laravel Server:
```bash
php artisan serve
```

Terminal 2 - Vite (for hot-reloading):
```bash
npm run dev
```

### Access the Application

Open your browser and navigate to:
```
http://localhost:8000
```

### Production Build

Build optimized frontend assets:

```bash
npm run build
```

---

## 👥 User Roles & Permissions

### Role Hierarchy

| Role | Access Level | Description |
|------|--------------|-------------|
| **Admin** | Full | System administrator with complete access |
| **Advisor** | Moderate | Faculty member who provides counseling |
| **Student** | Basic | Student seeking counseling appointments |

### Role-Specific Access

#### 🎒 Student
| Action | Allowed |
|--------|---------|
| View Dashboard | ✅ |
| Update Profile | ✅ |
| Search Advisors | ✅ |
| View Advisor Slots | ✅ |
| Book Appointments | ✅ |
| View Own Appointments | ✅ |
| Manage Slots | ❌ |
| Approve Appointments | ❌ |

#### 👨‍🏫 Advisor
| Action | Allowed |
|--------|---------|
| View Dashboard | ✅ |
| Update Profile | ✅ |
| Create Time Slots | ✅ |
| Delete Own Slots | ✅ |
| View Appointment Requests | ✅ |
| Approve/Decline Requests | ✅ |
| Book Appointments | ❌ |

#### 🔧 Admin
| Action | Allowed |
|--------|---------|
| All Advisor Permissions | ✅ |
| All Student Permissions | ✅ |
| System Administration | ✅ (Coming Soon) |

---

## 📝 Feature Details

### 1. User Registration & Authentication

The system uses **Laravel Breeze** for authentication, providing:

- **Registration**: Users can register with name, email, password, and role selection
- **Login**: Secure login with email and password
- **Password Reset**: Email-based password reset functionality
- **Email Verification**: Optional email verification support
- **Remember Me**: Persistent login sessions

### 2. Advisor Availability Management

Advisors can create time slots using the **Slot Splitter** functionality:

```
Input:
- Date: 2024-12-15
- Start Time: 09:00
- End Time: 12:00
- Duration: 30 minutes

Output:
- 09:00 - 09:30
- 09:30 - 10:00
- 10:00 - 10:30
- 10:30 - 11:00
- 11:00 - 11:30
- 11:30 - 12:00
```

**Features:**
- Automatic slot splitting based on duration
- Overlap detection prevents duplicate slots
- Visual slot status (Open/Booked)
- Easy slot deletion for unbooked slots

### 3. Student Booking Process

**Step 1: Find an Advisor**
- Browse all advisors or filter by department
- Search by advisor name
- View advisor details and department

**Step 2: Select a Time Slot**
- View all available slots for selected advisor
- Slots show date and time clearly
- Green slots indicate availability

**Step 3: Confirm Booking**
- Modal popup for booking confirmation
- Enter appointment purpose (required)
- Optional file attachment support
- Submit for advisor approval

**Step 4: Receive Token**
- Unique token generated (e.g., CSE-123-A)
- Token format: `DEPT_CODE-USER_ID-SERIAL`
- Token displayed in dashboard and appointments list

### 4. Appointment Token System

The system generates unique appointment tokens:

```
Token Format: [DEPARTMENT_CODE]-[USER_ID]-[SERIAL_LETTER]
Example: CSE-123-A

Components:
- CSE: Department code (e.g., Computer Science)
- 123: Student's user ID
- A: Random serial letter (A-Z)
```

### 5. Appointment Status Workflow

```
┌─────────────┐
│   PENDING   │ ← Initial state after booking
└──────┬──────┘
       │
       ▼
  ┌────┴────┐
  │         │
  ▼         ▼
┌─────┐  ┌──────┐
│APPROVED│  │DECLINED│
└────┬───┘  └────────┘
     │
     ▼
┌──────────┐
│COMPLETED │ (Future feature)
└──────────┘
```

### 6. Double-Booking Prevention

The system uses database-level locking to prevent race conditions:

```php
DB::transaction(function () {
    $slot = AppointmentSlot::where('id', $slotId)
        ->lockForUpdate()  // Pessimistic locking
        ->first();
    
    if ($slot->status !== 'active') {
        throw new Exception('Slot no longer available');
    }
    
    // Create appointment and mark slot as blocked
});
```

---

## 🛤 Routes & Endpoints

### Public Routes

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/` | Redirect to login |
| GET | `/login` | Login page |
| POST | `/login` | Process login |
| GET | `/register` | Registration page |
| POST | `/register` | Process registration |
| GET | `/forgot-password` | Password reset request |
| POST | `/forgot-password` | Send reset email |
| GET | `/reset-password/{token}` | Reset password form |
| POST | `/reset-password` | Process password reset |

### Authenticated Routes (All Users)

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/dashboard` | Main dashboard |
| GET | `/profile` | Edit profile |
| PATCH | `/profile` | Update profile |
| DELETE | `/profile` | Delete account |
| POST | `/logout` | Logout |

### Student Routes

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/student/advisors` | List all advisors |
| GET | `/student/advisors/{id}` | View advisor slots |
| POST | `/student/book` | Book an appointment |
| GET | `/student/my-appointments` | View my appointments |

### Advisor Routes

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/advisor/dashboard` | Pending requests |
| GET | `/advisor/slots` | Manage availability |
| POST | `/advisor/slots` | Create new slots |
| DELETE | `/advisor/slots/{slot}` | Delete a slot |
| PATCH | `/advisor/appointments/{id}` | Approve/Decline |

### Admin Routes (Coming Soon)

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/admin/dashboard` | Admin dashboard |

---

## 🗃 Database Schema

### Users Table

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    role ENUM('student', 'advisor', 'admin') DEFAULT 'student',
    university_id VARCHAR(255) UNIQUE,
    department_id BIGINT UNSIGNED,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);
```

### Departments Table

```sql
CREATE TABLE departments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(10) UNIQUE NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Appointment Slots Table

```sql
CREATE TABLE appointment_slots (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    advisor_id BIGINT UNSIGNED NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    is_recurring BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (advisor_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Appointments Table

```sql
CREATE TABLE appointments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT UNSIGNED NOT NULL,
    slot_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(255) UNIQUE,
    purpose TEXT NOT NULL,
    status ENUM('pending', 'approved', 'declined', 'completed', 'no_show', 'cancelled') DEFAULT 'pending',
    meeting_notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES appointment_slots(id) ON DELETE CASCADE
);
```

### Appointment Documents Table

```sql
CREATE TABLE appointment_documents (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    appointment_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
);
```

### Waitlists Table

```sql
CREATE TABLE waitlists (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    slot_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    is_notified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (slot_id) REFERENCES appointment_slots(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (slot_id, student_id)
);
```

### Entity Relationship Diagram

```
┌──────────────┐       ┌──────────────────┐       ┌──────────────┐
│  Department  │──────<│       User       │>──────│AppointmentSlot│
└──────────────┘   1:N └──────────────────┘ 1:N   └──────────────┘
      │                       │                          │
      │                       │1:N                       │1:1
      │                       ▼                          ▼
      │               ┌──────────────┐           ┌─────────────┐
      │               │ Appointment  │>──────────│   Waitlist  │
      │               └──────────────┘    1:N    └─────────────┘
      │                       │
      │                       │1:N
      │                       ▼
      │               ┌──────────────────┐
      └───────────────│AppointmentDocument│
                      └──────────────────┘
```

---

## 🔑 Default Credentials

After running `php artisan db:seed`, you can use these test accounts:

### Admin Account
```
Email: admin@uiu.ac.bd
Password: password
Role: Admin
```

### Student Account
```
Email: shaan@uiu.ac.bd
Password: password
Role: Student
University ID: 011223001
Department: CSE
```

### Advisor Account
```
Email: nabila@uiu.ac.bd
Password: password
Role: Advisor
University ID: T-9090
Department: CSE
```

> ⚠️ **Note**: Change these passwords immediately in production!

---

## 🧪 Testing

### Run All Tests

```bash
composer test
```

Or manually:

```bash
php artisan test
```

### Run Specific Test Suite

```bash
# Feature tests
php artisan test --testsuite=Feature

# Unit tests
php artisan test --testsuite=Unit
```

### Run with Coverage

```bash
php artisan test --coverage
```

### Test Directory Structure

```
tests/
├── Feature/           # Integration tests
│   └── ...
├── Unit/              # Unit tests
│   └── ...
└── TestCase.php       # Base test case
```

---

## 🌐 Deployment

### Production Checklist

1. **Environment Configuration**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   ```

2. **Generate Production Key**
   ```bash
   php artisan key:generate --force
   ```

3. **Optimize for Production**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Build Frontend Assets**
   ```bash
   npm run build
   ```

5. **Set Permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

6. **Configure Web Server**
   - Point document root to `CAMS/public`
   - Configure HTTPS
   - Set up proper rewrite rules

### Nginx Configuration Example

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/CAMS/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. **Fork the Repository**
   ```bash
   git fork https://github.com/5h444n/Counseling-Appointment-Management-System.git
   ```

2. **Create a Feature Branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```

3. **Make Your Changes**
   - Follow PSR-12 coding standards
   - Write meaningful commit messages
   - Add tests for new features

4. **Run Tests**
   ```bash
   composer test
   ```

5. **Format Code**
   ```bash
   ./vendor/bin/pint
   ```

6. **Commit Your Changes**
   ```bash
   git commit -m "Add: Amazing new feature"
   ```

7. **Push to Your Fork**
   ```bash
   git push origin feature/amazing-feature
   ```

8. **Open a Pull Request**

### Commit Message Convention

```
Add: New feature description
Fix: Bug fix description
Update: Modification description
Remove: Removal description
Docs: Documentation changes
Style: Code style changes (formatting, etc.)
Refactor: Code refactoring
Test: Adding or updating tests
```

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2025 Ahnaf Abid Shan

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 🙏 Acknowledgements

- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [Tailwind CSS](https://tailwindcss.com) - A utility-first CSS framework
- [Alpine.js](https://alpinejs.dev) - A rugged, minimal JavaScript framework
- [Vite](https://vitejs.dev) - Next generation frontend tooling
- [Laravel Breeze](https://laravel.com/docs/starter-kits#laravel-breeze) - Authentication scaffolding

---

<p align="center">
  Made with ❤️ for better academic counseling
</p>

<p align="center">
  <a href="https://github.com/5h444n/Counseling-Appointment-Management-System/issues">Report Bug</a>
  ·
  <a href="https://github.com/5h444n/Counseling-Appointment-Management-System/issues">Request Feature</a>
</p>