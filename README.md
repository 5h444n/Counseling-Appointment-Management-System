<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js">
  <img src="https://img.shields.io/badge/Vite-7.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
  <img src="https://img.shields.io/badge/Tests-410%20Passing-success?style=for-the-badge" alt="Tests">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="MIT License">
</p>

# 🎓 Counseling Appointment Management System (CAMS)

A comprehensive web-based appointment scheduling system designed for universities and educational institutions. CAMS streamlines the counseling and advisory appointment process between students and faculty advisors, enabling efficient booking, management, and tracking of counseling sessions.

> ✅ **Production Ready**: All critical and high-priority bugs have been fixed. See [BUGS.md](BUGS.md) for details.

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Key Features](#-key-features)
- [Tech Stack](#-tech-stack)
- [Prerequisites](#-prerequisites)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Running the Application](#-running-the-application)
- [User Roles & Access](#-user-roles--access)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Documentation](#-documentation)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🎯 Overview

CAMS is built to streamline appointment scheduling in academic institutions. The system provides:

- **For Students**: Browse advisors, book appointments in real-time, view appointment history, join waitlists, access resources, and submit feedback
- **For Advisors**: Manage availability slots, approve/decline requests, record meeting minutes, upload resources, and track student interactions
- **For Administrators**: Complete system oversight with user management, analytics dashboard, manual booking, system notices, and activity logs

Built with Laravel 12, Alpine.js, and Tailwind CSS for a modern, responsive, and intuitive user experience.

---

## ✨ Key Features

### 🎒 Student Features

- **Advisor Discovery** - Browse available advisors with department and specialization information
- **Real-time Booking** - View advisor availability and book appointments instantly
- **Appointment Management** - Track appointment status, view history, and manage upcoming sessions
- **Waitlist System** - Join waitlists for fully booked slots with automatic notifications
- **Appointment Cancellation** - Cancel appointments with automatic slot release
- **Resource Library** - Access educational materials shared by advisors
- **Feedback System** - Submit ratings and feedback for completed appointments
- **Personal Calendar** - Manage personal events and view all appointments
- **Notifications** - Real-time updates on appointment status changes

### 👨‍🏫 Advisor Features

- **Availability Management** - Create, edit, and delete appointment slots with flexible scheduling
- **Bulk Operations** - Create multiple slots at once for efficiency
- **Request Handling** - Review and approve/decline student appointment requests
- **Meeting Minutes** - Record and store meeting notes after each appointment
- **Document Management** - Upload resources and materials for student access
- **Student History** - View complete interaction history with students
- **Schedule Overview** - Comprehensive view of all appointments (past, present, future)
- **Calendar Integration** - Personal calendar for managing events

### 🔧 Admin Features

- **Analytics Dashboard** - System-wide statistics (top advisors, counseling hours, appointment trends)
- **User Management** - Faculty/advisor and student management (CRUD operations)
- **Manual Booking** - Create appointments on behalf of users
- **System Notices** - Create and manage announcements for specific user roles
- **Activity Logs** - Comprehensive audit trail with timestamps
- **Resource Management** - Centralized resource library
- **Data Export** - Export appointment data for reporting

### 🔐 Security & Quality

- **Role-Based Access Control** - Three distinct roles with specific permissions
- **Email Verification** - Required for all new accounts
- **Activity Logging** - All critical actions tracked
- **Rate Limiting** - Protection against abuse
- **Comprehensive Testing** - 410 passing tests with PHPUnit
- **File Security** - Document access restricted to authorized users

---

## 🛠 Tech Stack

### Backend
- **PHP** 8.2+ - Server-side language
- **Laravel** 12.x - Web application framework
- **Laravel Breeze** 2.x - Authentication scaffolding
- **PHPUnit** 11.x - Testing framework

### Frontend
- **Tailwind CSS** 3.x - Utility-first CSS framework
- **Alpine.js** 3.x - Lightweight JavaScript framework
- **Vite** 7.x - Build tool and dev server
- **Axios** 1.x - HTTP client

### Database
- **MySQL** (recommended) or **SQLite** (development/testing)

### Development Tools
- **Composer** - PHP dependency management
- **NPM** - JavaScript dependency management
- **Laravel Pint** - PHP code style fixer
- **Laravel Sail** - Docker development environment

---

## 📦 Prerequisites

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x
- **NPM** or **Yarn**
- **MySQL** >= 8.0 (or SQLite for development)

### Required PHP Extensions
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PCRE
- PDO
- Tokenizer
- XML

---

## 🚀 Installation

### Quick Setup (Recommended)

```bash
# 1. Clone the repository
git clone https://github.com/5h444n/Counseling-Appointment-Management-System.git
cd Counseling-Appointment-Management-System/CAMS

# 2. Run automated setup
composer run setup
```

This single command will:
- Install PHP dependencies
- Install JavaScript dependencies
- Create `.env` file from `.env.example`
- Generate application key
- Run database migrations
- Build frontend assets

### Manual Setup

If you prefer step-by-step installation:

```bash
# 1. Clone the repository
git clone https://github.com/5h444n/Counseling-Appointment-Management-System.git
cd Counseling-Appointment-Management-System/CAMS

# 2. Install PHP dependencies
composer install

# 3. Install JavaScript dependencies
npm install

# 4. Create environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Configure your database in .env (see Configuration section)

# 7. Run migrations
php artisan migrate

# 8. (Optional) Seed sample data
php artisan db:seed

# 9. Build frontend assets
npm run build
```

---

## ⚙️ Configuration

### Database Setup

Edit your `.env` file with database credentials:

**For MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cams_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

**For SQLite (Development):**
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

Then create the database:
```bash
# MySQL
mysql -u root -p
CREATE DATABASE cams_db;
exit;

# SQLite
touch database/database.sqlite
```

### Application Settings

```env
APP_NAME="CAMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC
```

### Mail Configuration (Optional)

For email notifications:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@cams.edu
MAIL_FROM_NAME="${APP_NAME}"
```

---

## ▶️ Running the Application

### Development Mode

Use the built-in development server with hot reloading:

```bash
composer run dev
```

This runs 4 concurrent processes:
- Laravel development server (http://localhost:8000)
- Queue worker for background jobs
- Laravel Pail for log monitoring
- Vite dev server for hot module replacement

**Alternative (separate terminals):**
```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Build assets with hot reload
npm run dev

# Terminal 3 (optional): Queue worker for notifications
php artisan queue:listen
```

### Access the Application

Open your browser and navigate to:
- **Application**: http://localhost:8000

### Default Credentials

After running seeders, you can log in with:

**Admin:**
- Email: `admin@example.com`
- Password: `password`

**Advisor:**
- Email: `advisor@example.com`
- Password: `password`

**Student:**
- Email: `student@example.com`
- Password: `password`

> ⚠️ **Important**: Change these credentials in production!

---

## 👥 User Roles & Access

### Admin
- Full system access
- User management (students, advisors)
- Analytics dashboard
- Manual appointment booking
- System notices
- Activity logs

### Advisor
- Availability management
- Appointment request handling
- Meeting minutes recording
- Resource uploads
- Student history viewing
- Schedule management

### Student
- Advisor browsing
- Appointment booking
- Waitlist management
- Appointment cancellation
- Resource access
- Feedback submission

### Middleware Protection

Routes are protected by role-based middleware:
- `IsAdmin` - Admin-only routes
- `IsAdvisor` - Advisor-only routes
- `IsStudent` - Student-only routes

---

## 🧪 Testing

CAMS includes comprehensive test coverage with 410 passing tests.

### Run All Tests

```bash
composer test
# or
php artisan test
```

### Run Specific Test Suites

```bash
# Feature tests only
php artisan test --testsuite=Feature

# Unit tests only
php artisan test --testsuite=Unit

# Specific test file
php artisan test tests/Feature/StudentBookingTest.php
```

### Test Coverage Includes

- Authentication flows (login, registration, password reset)
- Appointment booking and cancellation
- Slot creation and management
- Waitlist functionality
- Admin CRUD operations
- Middleware and authorization
- Activity logging
- File uploads
- Email notifications

---

## 🌐 Deployment

### Production Checklist

Before deploying to production:

1. **Update Environment**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   ```

2. **Generate Production Key**
   ```bash
   php artisan key:generate --force
   ```

3. **Install Dependencies**
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install --production
   ```

4. **Build Assets**
   ```bash
   npm run build
   ```

5. **Optimize Laravel**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

6. **Set Permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

7. **Configure Web Server**
   - Point document root to `CAMS/public`
   - Configure HTTPS with SSL certificate
   - Set up proper URL rewrite rules

8. **Set Up Cron Jobs**
   ```bash
   * * * * * cd /path-to-cams && php artisan schedule:run >> /dev/null 2>&1
   ```

9. **Configure Queue Worker**
   ```bash
   # Using supervisor (recommended)
   sudo supervisorctl start cams-worker
   ```

### Web Server Configuration

#### Nginx Example

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

#### Apache Example

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/CAMS/public

    <Directory /var/www/CAMS/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/cams-error.log
    CustomLog ${APACHE_LOG_DIR}/cams-access.log combined
</VirtualHost>
```

---

## 📚 Documentation

Additional documentation is available:

- **[BUGS.md](BUGS.md)** - Known issues and bug status (12 low-priority items remaining)
- **[CAMS/TEST_REPORT.md](CAMS/TEST_REPORT.md)** - Detailed test coverage report
- **[CAMS/QA_SUMMARY.md](CAMS/QA_SUMMARY.md)** - Quality assurance summary
- **[CAMS/SUGGESTIONS.md](CAMS/SUGGESTIONS.md)** - Future feature recommendations
- **[PROJECT_STATUS_REPORT.md](PROJECT_STATUS_REPORT.md)** - Project status overview
- **[COMPREHENSIVE_TEST_REPORT.md](COMPREHENSIVE_TEST_REPORT.md)** - Comprehensive testing details

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

### How to Contribute

1. **Fork the Repository**
   ```bash
   git clone https://github.com/your-username/Counseling-Appointment-Management-System.git
   ```

2. **Create a Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make Your Changes**
   - Write clean, documented code
   - Follow PSR-12 coding standards
   - Add tests for new features
   - Update documentation as needed

4. **Run Tests**
   ```bash
   composer test
   ./vendor/bin/pint  # Code style fixer
   ```

5. **Commit Your Changes**
   ```bash
   git add .
   git commit -m "Add: your feature description"
   ```

6. **Push to Your Fork**
   ```bash
   git push origin feature/your-feature-name
   ```

7. **Open a Pull Request**
   - Provide a clear description of changes
   - Reference any related issues
   - Ensure all tests pass

### Code Style

This project follows PSR-12 coding standards. Run Laravel Pint before committing:

```bash
./vendor/bin/pint
```

### Reporting Issues

Found a bug? Please open an issue with:
- Clear description of the problem
- Steps to reproduce
- Expected vs actual behavior
- System information (PHP version, Laravel version, etc.)

---

## 📄 License

This project is open-sourced software licensed under the [MIT License](LICENSE).

---

## 🙏 Acknowledgments

Built with:
- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [Tailwind CSS](https://tailwindcss.com) - A utility-first CSS framework
- [Alpine.js](https://alpinejs.dev) - Your new, lightweight, JavaScript framework
- [Vite](https://vitejs.dev) - Next Generation Frontend Tooling

---

## 📞 Support

For questions, issues, or feature requests:
- **Issues**: [GitHub Issues](https://github.com/5h444n/Counseling-Appointment-Management-System/issues)
- **Repository**: [GitHub Repository](https://github.com/5h444n/Counseling-Appointment-Management-System)

---

<p align="center">Made with ❤️ for educational institutions</p>
