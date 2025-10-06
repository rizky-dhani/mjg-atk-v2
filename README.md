# MJG ATK Management System

## Overview

The MJG ATK Management System is a comprehensive inventory management solution for office stationery (Alat Tulis Kantor/ATK) built with Laravel and Filament v4. This system enables organizations to efficiently manage their office supplies inventory across multiple divisions with a robust approval workflow.

## Features

### 1. User Management
- Role-based access control (RBAC) with Spatie Permissions
- Support for multiple user roles: Super Admin, Admin, Head, and Staff
- Division-based organization structure

### 2. Inventory Management
- **ATK Categories**: Organize items by category
- **ATK Items**: Manage individual office supply items
- **Division Stocks**: Track inventory levels per division
- **Stock Requests**: Request additional inventory
- **Stock Usages**: Record consumption of inventory

### 3. Approval Workflow
- Configurable multi-step approval processes
- Role and division-based approval steps
- Comprehensive audit trail

### 4. Reporting & Analytics
- Real-time inventory tracking
- Usage analytics
- Approval status monitoring

## System Requirements

- PHP 8.2+
- Laravel 12+
- MySQL 5.7+ or MariaDB 10.2+
- Composer
- Node.js and NPM

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/mjg-atk-v2.git
   cd mjg-atk-v2
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node dependencies:
   ```bash
   npm install
   npm run build
   ```

4. Copy and configure the environment file:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Configure your database settings in `.env`

6. Run database migrations and seeders:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. Start the development server:
   ```bash
   php artisan serve
   ```

## Roles and Permissions

### Super Admin
- Full system access
- User management
- Role and permission management
- All administrative functions

### Admin
- Manage ATK categories and items
- View and manage all divisions
- Approve/reject stock requests and usages
- Manage approval workflows

### Head
- View division inventory
- Request stock for their division
- Approve/reject stock requests and usages within their authority

### Staff
- View available inventory
- Request stock for their division

## Core Components

### 1. Divisions
Organizational units that hold and consume inventory.

### 2. ATK Categories
Classification system for organizing office supplies.

### 3. ATK Items
Individual office supply items with details like name, description, and unit of measure.

### 4. Division Stocks
Current inventory levels for each item in each division.

### 5. Stock Requests
Formal requests for additional inventory submitted by divisions.

### 6. Stock Usages
Records of inventory consumption by divisions.

### 7. Approval Flows
Configurable workflows that define the approval process for requests and usages.

## API Endpoints

The system provides RESTful APIs for integration with external systems. All endpoints are protected by authentication.

## Testing

Run the test suite with:
```bash
php artisan test
```

## Deployment

For production deployment:

1. Set `APP_ENV=production` in `.env`
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Run `php artisan view:cache`
5. Configure your web server (Apache/Nginx) to point to the `public` directory

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please contact the development team or open an issue on GitHub.
