<div align="center">

# 🎓 Counseling Appointment Management System (CAMS)

### A Modern, Full-Featured Appointment Scheduling Platform for Educational Institutions

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)](https://alpinejs.dev)
[![Tests](https://img.shields.io/badge/Tests-410%20Passing-success?style=for-the-badge)](https://github.com/5h444n/Counseling-Appointment-Management-System)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

[Features](#-features) • [Installation](#-installation) • [Documentation](#-documentation) • [Contributing](#-contributing) • [License](#-license)

</div>

---

## 📖 Overview

**CAMS** (Counseling Appointment Management System) is a comprehensive, production-ready web application designed to streamline the counseling and advisory appointment process in universities and educational institutions. Built with modern web technologies, CAMS provides an intuitive, responsive platform that connects students with faculty advisors efficiently.

### 🎯 Core Objectives

- **Simplify Scheduling** - Make booking counseling appointments effortless for students
- **Optimize Advisor Time** - Provide advisors with powerful tools to manage their availability
- **Enhance Communication** - Facilitate better student-advisor interactions through structured appointment management
- **Track Engagement** - Enable administrators to monitor and analyze counseling services usage
- **Ensure Accessibility** - Deliver a responsive, user-friendly interface accessible on any device

### 🏆 Production Ready

✅ **410 Comprehensive Tests** - Extensive test coverage ensures reliability  
✅ **Security Hardened** - Role-based access control, activity logging, and secure file handling  
✅ **Bug-Free Core** - All critical and high-priority bugs resolved ([see BUGS.md](BUGS.md))  
✅ **Performance Optimized** - Efficient database queries and asset optimization  

---

## ✨ Features

### 👨‍🎓 For Students

<table>
<tr>
<td width="50%">

**Appointment Management**
- 📅 Browse advisors by department and specialization
- ⚡ Real-time availability viewing and instant booking
- 📊 Track appointment status and view complete history
- 🔔 Receive notifications for status changes
- ❌ Cancel appointments with automatic slot release

</td>
<td width="50%">

**Enhanced Experience**
- 🎫 Join waitlists for fully booked slots
- 📚 Access shared resources and materials
- ⭐ Submit ratings and feedback
- 📆 Manage personal calendar events
- 🔍 Search and filter advisors

</td>
</tr>
</table>

### 👨‍🏫 For Advisors

<table>
<tr>
<td width="50%">

**Availability & Scheduling**
- 🕐 Create flexible appointment slots
- ⚡ Bulk slot creation for efficiency
- ✅ Approve or decline appointment requests
- 📝 Record detailed meeting minutes
- 🗓️ Comprehensive schedule overview

</td>
<td width="50%">

**Student Engagement**
- 👥 View complete student interaction history
- 📤 Upload and share educational resources
- 📊 Track appointment statistics
- 📆 Personal calendar integration
- 🔍 Search appointment records

</td>
</tr>
</table>

### 🔧 For Administrators

<table>
<tr>
<td width="50%">

**System Management**
- 👥 User management (students, advisors, faculty)
- 📊 Analytics dashboard with key metrics
- 📅 Manual appointment booking capabilities
- 📢 System-wide notices and announcements
- 📋 Activity logs and audit trails

</td>
<td width="50%">

**Insights & Control**
- 📈 Top advisors and counseling hours tracking
- 📊 Appointment trends analysis
- 📁 Centralized resource management
- 📥 Data export functionality
- 🔒 System security monitoring

</td>
</tr>
</table>

### 🔐 Security & Quality Assurance

- **Role-Based Access Control (RBAC)** - Three distinct user roles with granular permissions
- **Email Verification** - Mandatory verification for all new accounts
- **Activity Logging** - Comprehensive audit trail for all critical actions
- **Rate Limiting** - Protection against API abuse and brute-force attacks
- **Secure File Handling** - Document access restricted to authorized users only
- **Comprehensive Testing** - 410 automated tests covering all critical functionality

---

## 🛠️ Technology Stack

### Backend Technologies

| Technology | Version | Purpose |
|-----------|---------|---------|
| **PHP** | 8.2+ | Server-side programming language |
| **Laravel** | 12.x | Full-featured web application framework |
| **Laravel Breeze** | 2.x | Authentication and authorization scaffolding |
| **PHPUnit** | 11.x | Unit and feature testing framework |
| **MySQL/SQLite** | 8.0+/3.x | Relational database management |

### Frontend Technologies

| Technology | Version | Purpose |
|-----------|---------|---------|
| **Tailwind CSS** | 3.x | Utility-first CSS framework for modern UI |
| **Alpine.js** | 3.x | Lightweight reactive framework |
| **Vite** | 7.x | Next-generation frontend build tool |
| **Axios** | 1.x | Promise-based HTTP client |

### Development Tools

- **Composer** - PHP dependency management
- **NPM** - JavaScript package management
- **Laravel Pint** - Opinionated PHP code style fixer
- **Laravel Sail** - Docker-based development environment
- **Concurrently** - Run multiple commands simultaneously

---

## 📋 Prerequisites

### System Requirements

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x
- **NPM** or **Yarn**
- **MySQL** >= 8.0 (or SQLite for development/testing)

### Required PHP Extensions

```
BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, 
OpenSSL, PCRE, PDO, Tokenizer, XML
```

To check your PHP extensions:
```bash
php -m
```

---

## 🚀 Installation

### Option 1: Quick Setup (Recommended)

The fastest way to get started - one command does it all:

```bash
# Clone the repository
git clone https://github.com/5h444n/Counseling-Appointment-Management-System.git
cd Counseling-Appointment-Management-System/CAMS

# Automated setup
composer run setup
```

**What this does:**
- ✅ Installs all PHP dependencies
- ✅ Installs all JavaScript dependencies
- ✅ Creates `.env` configuration file
- ✅ Generates application encryption key
- ✅ Runs database migrations
- ✅ Builds production-ready frontend assets

### Option 2: Manual Step-by-Step Setup

For those who prefer more control:

```bash
# 1. Clone the repository
git clone https://github.com/5h444n/Counseling-Appointment-Management-System.git
cd Counseling-Appointment-Management-System/CAMS

# 2. Install backend dependencies
composer install

# 3. Install frontend dependencies
npm install

# 4. Create environment configuration
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Configure database (edit .env file)
# See Configuration section below

# 7. Run database migrations
php artisan migrate

# 8. (Optional) Seed sample data
php artisan db:seed

# 9. Build frontend assets
npm run build
```

---

## ⚙️ Configuration

### Database Configuration

Edit your `.env` file with the appropriate database credentials:

#### MySQL Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cams_db
DB_USERNAME=root
DB_PASSWORD=your_secure_password
```

Create the MySQL database:
```bash
mysql -u root -p
CREATE DATABASE cams_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

#### SQLite Configuration (Development)

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

Create the SQLite database:
```bash
touch database/database.sqlite
```

### Application Settings

Configure essential application settings in `.env`:

```env
APP_NAME="CAMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
```

### Mail Configuration (Optional)

For email notifications and password resets:

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

**Popular SMTP Providers:**
- [Mailtrap](https://mailtrap.io) - Development email testing
- [SendGrid](https://sendgrid.com) - Production email delivery
- [Amazon SES](https://aws.amazon.com/ses/) - Scalable email service
- [Mailgun](https://www.mailgun.com) - Email API service

---

## ▶️ Running the Application

### Development Mode with Hot Reload

The easiest way to run CAMS in development:

```bash
composer run dev
```

This single command starts four concurrent processes:
1. 🌐 **Laravel Development Server** - `http://localhost:8000`
2. ⚙️ **Queue Worker** - Processes background jobs and notifications
3. 📝 **Laravel Pail** - Real-time log monitoring
4. ⚡ **Vite Dev Server** - Hot module replacement for frontend assets

### Alternative: Manual Process Management

Run each process in separate terminal windows:

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Build assets with hot reload
npm run dev

# Terminal 3: Queue worker for background jobs
php artisan queue:listen

# Terminal 4 (Optional): Monitor logs
php artisan pail
```

### Accessing the Application

Open your web browser and navigate to:

**🌐 Application URL:** [http://localhost:8000](http://localhost:8000)

### Default Credentials

After running database seeders (`php artisan db:seed`):

| Role | Email | Password |
|------|-------|----------|
| **Administrator** | admin@example.com | password |
| **Advisor** | advisor@example.com | password |
| **Student** | student@example.com | password |

> ⚠️ **Security Warning:** Change these default credentials immediately in production environments!

---

## 👥 User Roles & Permissions

### 🔐 Access Control Matrix

| Feature | Admin | Advisor | Student |
|---------|-------|---------|---------|
| View Analytics Dashboard | ✅ | ❌ | ❌ |
| Manage Users | ✅ | ❌ | ❌ |
| Create System Notices | ✅ | ❌ | ❌ |
| Manual Appointment Booking | ✅ | ❌ | ❌ |
| View Activity Logs | ✅ | ❌ | ❌ |
| Create Availability Slots | ❌ | ✅ | ❌ |
| Approve/Decline Requests | ❌ | ✅ | ❌ |
| Record Meeting Minutes | ❌ | ✅ | ❌ |
| Upload Resources | ✅ | ✅ | ❌ |
| Book Appointments | ❌ | ❌ | ✅ |
| Join Waitlists | ❌ | ❌ | ✅ |
| Submit Feedback | ❌ | ❌ | ✅ |
| View Own Appointments | ✅ | ✅ | ✅ |

### Middleware Protection

Routes are protected using role-based middleware:
- `IsAdmin` - Restricts access to administrator-only routes
- `IsAdvisor` - Restricts access to advisor-only routes  
- `IsStudent` - Restricts access to student-only routes

---

## 🧪 Testing

CAMS includes a comprehensive test suite with **410 passing tests** covering all critical functionality.

### Running Tests

```bash
# Run all tests
composer test
# or
php artisan test

# Run with coverage report
php artisan test --coverage

# Run tests in parallel (faster)
php artisan test --parallel
```

### Test Suites

```bash
# Feature tests only
php artisan test --testsuite=Feature

# Unit tests only
php artisan test --testsuite=Unit

# Run specific test file
php artisan test tests/Feature/StudentBookingTest.php

# Run specific test method
php artisan test --filter testStudentCanBookAppointment
```

### Test Coverage Areas

- ✅ **Authentication** - Login, registration, password reset, email verification
- ✅ **Appointments** - Booking, cancellation, status updates, history
- ✅ **Availability** - Slot creation, deletion, bulk operations
- ✅ **Waitlists** - Joining, notifications, automatic booking
- ✅ **Authorization** - Role-based access control, middleware
- ✅ **Admin Operations** - User CRUD, analytics, manual booking
- ✅ **File Management** - Resource uploads, access control
- ✅ **Activity Logging** - Audit trail creation and retrieval
- ✅ **Notifications** - Email sending, notification queuing

### Continuous Integration

Tests run automatically on every pull request. View detailed test reports in:
- [COMPREHENSIVE_TEST_REPORT.md](COMPREHENSIVE_TEST_REPORT.md)
- [TEST_COVERAGE_REPORT.md](TEST_COVERAGE_REPORT.md)

---

## 🌐 Deployment

### Production Deployment Checklist

#### 1. Environment Configuration

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

#### 2. Generate Production Application Key

```bash
php artisan key:generate --force
```

#### 3. Install Dependencies (Production Mode)

```bash
composer install --optimize-autoloader --no-dev
npm install --production
```

#### 4. Build Frontend Assets

```bash
npm run build
```

#### 5. Optimize Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

#### 6. Set Directory Permissions

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 7. Configure Web Server

Point your web server's document root to the `public` directory:

**Nginx Configuration Example**

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /var/www/CAMS/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

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
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Apache Configuration Example**

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAdmin admin@your-domain.com
    DocumentRoot /var/www/CAMS/public

    <Directory /var/www/CAMS/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/cams-error.log
    CustomLog ${APACHE_LOG_DIR}/cams-access.log combined
</VirtualHost>
```

#### 8. SSL Configuration

Secure your application with HTTPS:

```bash
# Using Certbot (Let's Encrypt)
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

#### 9. Set Up Scheduled Tasks

Add to crontab (`crontab -e`):

```cron
* * * * * cd /path-to-cams && php artisan schedule:run >> /dev/null 2>&1
```

#### 10. Configure Queue Worker

Using Supervisor for process management:

```ini
[program:cams-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/CAMS/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/CAMS/storage/logs/worker.log
stopwaitsecs=3600
```

Start the worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start cams-worker:*
```

---

## 📚 Documentation

Comprehensive documentation is available in the repository:

| Document | Description |
|----------|-------------|
| [BUGS.md](BUGS.md) | Known issues and bug tracking (12 low-priority items) |
| [PROJECT_STATUS_REPORT.md](PROJECT_STATUS_REPORT.md) | Overall project status and milestones |
| [COMPREHENSIVE_TEST_REPORT.md](COMPREHENSIVE_TEST_REPORT.md) | Detailed testing documentation |
| [TEST_COVERAGE_REPORT.md](TEST_COVERAGE_REPORT.md) | Test coverage analysis |
| [CAMS/TEST_REPORT.md](CAMS/TEST_REPORT.md) | Application-specific test results |
| [CAMS/QA_SUMMARY.md](CAMS/QA_SUMMARY.md) | Quality assurance summary |
| [CAMS/SUGGESTIONS.md](CAMS/SUGGESTIONS.md) | Future enhancement recommendations |

---

## 🤝 Contributing

Contributions are welcome and greatly appreciated! Here's how you can help:

### Getting Started

1. **Fork the Repository**
   ```bash
   git clone https://github.com/your-username/Counseling-Appointment-Management-System.git
   cd Counseling-Appointment-Management-System
   ```

2. **Create a Feature Branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```

3. **Make Your Changes**
   - Write clean, well-documented code
   - Follow PSR-12 coding standards
   - Add tests for new functionality
   - Update documentation as needed

4. **Run Tests**
   ```bash
   composer test
   ./vendor/bin/pint  # Fix code style
   ```

5. **Commit Your Changes**
   ```bash
   git add .
   git commit -m "Add: amazing feature description"
   ```

6. **Push to Your Fork**
   ```bash
   git push origin feature/amazing-feature
   ```

7. **Open a Pull Request**
   - Provide a clear description of changes
   - Reference related issues
   - Ensure all tests pass

### Code Style

This project follows PSR-12 coding standards. Before committing, run:

```bash
./vendor/bin/pint
```

### Commit Message Convention

Follow conventional commits:
- `Add:` - New features
- `Fix:` - Bug fixes
- `Update:` - Updates to existing features
- `Remove:` - Removed features or files
- `Docs:` - Documentation changes
- `Test:` - Test additions or modifications
- `Refactor:` - Code refactoring

### Reporting Bugs

Found a bug? Please open an issue with:
- Clear, descriptive title
- Detailed description of the problem
- Steps to reproduce
- Expected vs actual behavior
- System information (PHP version, OS, etc.)
- Screenshots (if applicable)

### Suggesting Enhancements

Have an idea? Open an issue with:
- Clear description of the enhancement
- Use cases and benefits
- Possible implementation approach

---

## 📄 License

This project is open-source software licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

### MIT License Summary

- ✅ Commercial use allowed
- ✅ Modification allowed
- ✅ Distribution allowed
- ✅ Private use allowed
- ⚠️ Liability and warranty limitations apply

---

## 🙏 Acknowledgments

CAMS is built with amazing open-source technologies:

- **[Laravel](https://laravel.com)** - The elegant PHP framework for web artisans
- **[Tailwind CSS](https://tailwindcss.com)** - A utility-first CSS framework for rapid UI development
- **[Alpine.js](https://alpinejs.dev)** - A rugged, minimal framework for composing behavior in your markup
- **[Vite](https://vitejs.dev)** - Next generation frontend tooling with instant server start
- **[PHP](https://php.net)** - A popular general-purpose scripting language especially suited to web development

---

## 📞 Support & Contact

### Getting Help

- 📖 **Documentation** - Check the [documentation](#-documentation) section
- 🐛 **Bug Reports** - [Open an issue](https://github.com/5h444n/Counseling-Appointment-Management-System/issues)
- 💡 **Feature Requests** - [Submit an enhancement request](https://github.com/5h444n/Counseling-Appointment-Management-System/issues)
- 💬 **Discussions** - [Join the conversation](https://github.com/5h444n/Counseling-Appointment-Management-System/discussions)

### Links

- **Repository**: [github.com/5h444n/Counseling-Appointment-Management-System](https://github.com/5h444n/Counseling-Appointment-Management-System)
- **Issues**: [github.com/5h444n/Counseling-Appointment-Management-System/issues](https://github.com/5h444n/Counseling-Appointment-Management-System/issues)

---

## 📊 Project Statistics

- **Tests**: 410 passing
- **Lines of Code**: ~15,000+
- **Database Tables**: 15+
- **Routes**: 100+
- **Middleware**: 5 custom
- **Controllers**: 20+

---

## 🗺️ Roadmap

Future enhancements under consideration:

- 📱 Mobile application (iOS/Android)
- 🔌 Calendar integrations (Google Calendar, Outlook)
- 🌍 Multi-language support (i18n)
- 📹 Video conferencing integration
- 📊 Advanced analytics and reporting
- 🔔 SMS notification support
- 📧 Email template customization
- 🎨 Theme customization options

See [CAMS/SUGGESTIONS.md](CAMS/SUGGESTIONS.md) for detailed future feature recommendations.

---

<div align="center">

### Made with ❤️ for Educational Institutions

**Empowering better student-advisor connections through technology**

[⬆ Back to Top](#-counseling-appointment-management-system-cams)

</div>
