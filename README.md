# Leave-management

# Leave Management System

A modern, enterprise-grade **leave management platform** built with Symfony 7.3 and cutting-edge web technologies. Designed to streamline employee leave requests, approvals, and calendar tracking with role-based access control.

---

## 🎯 Core Features

### 📅 **Intelligent Calendar Management**

- Interactive calendar interface for visualizing all leave requests
- Real-time status filtering (Pending, Approved, Rejected, Consumed)
- Admin dashboard with comprehensive leave analytics
- Automatic status transitions (approved leaves become "consumed" after expiration)

### 📋 **Leave Request & Management System**

- Multi-type leave requests (Vacation, Sick Leave, etc.)
- Request submission with optional supporting documentation
- Built-in workflow: Request → Review → Approval/Rejection
- Detailed leave history with audit trails
- Refusal reasons and approval notes tracking

### 👥 **Role-Based Access Control**

Hierarchical permission system with three tiers:

```
ROLE_EMPLOYEE (Base)
├── Can request leaves
├── View own calendar
└── Track request status

ROLE_MANAGER (Inherits ROLE_EMPLOYEE)
├── Approve/reject team member leaves
├── View team calendar
└── Process leave requests

ROLE_ADMIN (Full Access)
├── Global calendar overview
├── User and role management
├── System configuration
└── Analytics & reporting
```

### 🔐 **Security & Authentication**

- **Form-based login** with CSRF protection enabled
- **Custom authentication handlers**:
    - `LoginSuccessHandler`: Redirects users based on role
    - `LoginFailureHandler`: Logs failed authentication attempts
- **Entity-based User Provider** with email authentication
- **Password hashing** using Symfony's auto-detection
- Secure session management with logout CSRF tokens
- User inheritance model with `Manager` subclass for department heads

---

## 🛠 Technology Stack

### **Backend**

- **Framework**: Symfony 7.3 (LTS Ready)
- **ORM**: Doctrine ORM 3.5 with DBAL 3
- **Validation**: Symfony Validator with custom rules
- **Database Migrations**: Doctrine Migrations Bundle (versioned schema)

### **Frontend**

- **Build Tool**: Webpack Encore
- **CSS Framework**: Tailwind CSS 3
- **Interactivity**: Stimulus JS (CSRF Protection Controller)
- **Real-time Updates**: Symfony UX Turbo for dynamic navigation

### **Security Packages**

- `symfony/security-bundle` - Core authentication
- `symfony/security-http` - HTTP security layer
- CSRF token generation and validation

---

## 🐳 Docker & Deployment

### **Local Development**

```yaml
# compose.yaml - Quick startup
services:
    php:
        image: php:8.2-cli
        ports: ["8000:8000"]
        depends_on: [database]
        command: ["php", "-S", "0.0.0.0:8000", "-t", "public"]

    database:
        image: postgres:16-alpine
        environment:
            POSTGRES_DB: leave-management
            POSTGRES_USER: s*****
            POSTGRES_PASSWORD: ****
```

Run locally:

```bash
docker-compose up -d
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### **Production Deployment**

Multi-stage Dockerfile with optimized layer caching:

```dockerfile
FROM php:8.4-fpm-bookworm AS build
RUN apt-get update && apt-get install -y \
    libicu-dev libxml2-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev

FROM php:8.4-fpm-bookworm
COPY --from=build /usr/local/lib/php/extensions /usr/local/lib/php/extensions
```

**Infrastructure**:

- Nginx reverse proxy configuration in `deployments/nginx-site.conf`
- Kubernetes deployment manifests (`symfony-deployment.yaml`)
- Supervisor process management for background workers
- PostgreSQL 16 Alpine for database

---

## 📁 Project Architecture

```
src/
├── Controller/          # Request handlers with attribute routing
│   ├── CalendarController.php     # Calendar visualization & filtering
│   ├── DashboardController.php    # User dashboard with auto-status updates
│   ├── Leave/                     # Leave request workflows
│   └── Authentification/          # Login/logout/registration
├── Entity/             # Doctrine ORM entities
│   ├── User.php        # Base user with inheritance
│   ├── Manager.php     # Manager subclass (team leads)
│   ├── Leave.php       # Leave requests (status, dates, proof)
│   ├── Team.php        # Department/team organization
│   └── Notification.php
├── Enum/               # Type-safe enumerations
│   ├── Role.php        # ROLE_EMPLOYEE, ROLE_MANAGER, ROLE_ADMIN
│   ├── LeaveType.php   # Vacation, Sick, Compassion, etc.
│   ├── RequestStatus.php # Pending, Approved, Rejected, Consumed
│   └── Departement.php
├── Form/               # Symfony form types with validation
│   ├── LeaveFormType.php
│   └── UserFormType.php
├── Repository/         # Custom database queries
└── Security/           # Authentication handlers & voters

config/
├── packages/security.yaml    # Role hierarchy & access control
├── routes.yaml              # Application routing
└── services.yaml            # Service container configuration

templates/
├── base.html.twig           # Layout with CSS/JS
├── security/                # Login/register views
├── dashboard/               # User dashboard
├── calendar/                # Calendar interface
└── leave/                   # Leave request forms
```

---

## 🔑 Key Code Examples

### **Role-Based Access Control**

```php
// In CalendarController.php
#[Route('/calendar', name: 'app_calendar')]
#[IsGranted('ROLE_ADMIN')]
public function index(Request $request, EntityManagerInterface $em): Response
{
    $selectedStatuses = $request->query->all('statuses')
        ?: [RequestStatus::ACCEPTE->value];

    $leaves = $em->getRepository(Leave::class)
        ->createQueryBuilder('l')
        ->where($qb->expr()->in('l.status', ':statuses'))
        ->setParameter('statuses', $statusEnums)
        ->getQuery()
        ->getResult();
}
```

### **Automatic Leave Status Management**

```php
// In DashboardController.php - Auto-consume approved leaves
$today = new \DateTimeImmutable('today');
foreach ($leaves as $leave) {
    if ($leave->getStatus()->value === 'approuvée'
        && $leave->getEndDate() < $today) {
        $leave->setStatus(RequestStatus::CONSOMMEE);
        $updated = true;
    }
}
```

### **Leave Entity with Audit Trail**

```php
// Leave.php - Full workflow tracking
class Leave {
    private RequestStatus $status;           // Current status
    private \DateTimeInterface $requestDate; // When requested
    private ?\DateTimeInterface $processDate;// When processed
    private ?string $processedBy;           // Who processed it
    private ?string $refusalReason;         // Why rejected (if applicable)
    private ?string $proof;                 // Supporting documents
}
```

---

## 📊 Database Schema

Relational structure with Doctrine inheritance:

- **User** (Base entity with JOINED inheritance)
    - Manager (extends User) - Department heads with approval rights
- **Leave** - Leave requests with full workflow state
- **Team** - Department/team organization
- **Notification** - Leave status updates

All tables support multi-tenancy patterns and audit logging.

---

## 🚀 Getting Started

```bash
# Clone repository
git clone <repo>
cd leavemanagement

# Install dependencies
composer install
npm install

# Database setup
docker-compose up -d
php bin/console doctrine:migrations:migrate

# Load sample data
php bin/console doctrine:fixtures:load

# Build assets
npm run build

# Start dev server
docker-compose up
# Visit http://localhost:8000
```

---

## 🔬 Development Highlights

✅ **Enterprise Architecture**: Doctrine ORM with repository pattern  
✅ **Type Safety**: Enums for roles, statuses, and leave types  
✅ **Form Validation**: Custom validators for date ranges and business logic  
✅ **Security First**: CSRF protection, password hashing, role-based authorization  
✅ **Scalable**: Doctrine migrations, containerized deployment, Kubernetes-ready  
✅ **Modern Frontend**: Tailwind CSS, Stimulus JS, Webpack Encore  
✅ **Production Ready**: Multi-stage Docker builds, Nginx config, Supervisor management

---

## 📜 Requirements

- **PHP** 8.2+
- **Composer** 2.0+
- **Node.js** 18+
- **PostgreSQL** 16+
- **Docker** & Docker Compose (optional, for containerized setup)

---

## 📝 License

Proprietary - All rights reserved

---

**Built for scalability, security, and user experience.** Perfect for organizations managing employee time-off at scale.
