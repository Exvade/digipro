# Dashboard Inject - Production Management System

A comprehensive production management system for PT Mada Wikri Tunggal Plant 3, specifically designed for injection molding operations.

## Features

### Dashboard Inject
- **Machine Monitoring**: Real-time status monitoring of injection molding machines
- **Step-by-Step Checksheet**: Guided form process with validation
- **Photo Documentation**: Camera integration for quality documentation
- **Multi-Step Forms**: 
  - APD (Alat Pelindung Diri) - Safety equipment verification
  - Quality Control - Q Point verification
  - Work Instructions - Instruction availability check
  - Sample Limits - Sample limit verification
- **Responsive Design**: Mobile-friendly interface

### Form Steps
1. **APD Form**: 
   - Topi (Helmet) usage verification
   - Sepatu Safety (Safety shoes) usage verification
2. **Quality Form**: 
   - Q Point availability check
3. **Work Instructions Form**: 
   - Work instruction availability check
4. **Sample Limits Form**: 
   - Sample limit availability check
5. **Photo Documentation**: 
   - Camera capture for each verification step

## Installation

### Prerequisites
- PHP 8.0 or higher
- Composer
- MySQL/MariaDB
- Web server (Apache/Nginx)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd DIGIPRO
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   Edit `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=digipro
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Seed database with sample data**
   ```bash
   php artisan db:seed --class=DashboardSeeder
   ```

7. **Create storage link**
   ```bash
   php artisan storage:link
   ```

8. **Set permissions**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

## Usage

### Accessing the System
1. Navigate to your application URL
2. Login with demo credentials:
   - Username: `superadmin`
   - Password: `password`

### Using the Dashboard
1. **Select Group**: Choose group A, B, or C before proceeding
2. **View Machine Status**: Monitor machine status (Active, Maintenance, Offline)
3. **Create Checksheet**: Click on any machine to start the checksheet process
4. **Follow Form Steps**: Complete each step sequentially:
   - APD verification
   - Quality control check
   - Work instruction verification
   - Sample limit verification
   - Photo documentation
5. **Submit**: Save the checksheet with all documentation

### User Roles
- **Super Admin**: Full access to all features
- **Kabag**: Department head access
- **Manager Plant**: Plant management access
- **Leader**: Team leader access (specific to department)
- **Operator**: Basic operator access

## Database Structure

### Main Tables
- `users`: User accounts and roles
- `mesin`: Machine information and status
- `parts`: Part catalog
- `checksheet`: Main checksheet records
- `checksheet_photos`: Photo documentation
- `meja`: Work station information

### Key Fields
- **APD Fields**: `apd_topi`, `apd_sepatu`
- **Quality Fields**: `quality_qpoint`
- **Instruction Fields**: `instruksi_kerja`
- **Sample Fields**: `limit_sampel`

## API Endpoints

### Dashboard Routes
- `GET /dashboard/inject` - Main dashboard
- `POST /dashboard/inject/store` - Store checksheet
- `GET /dashboard/inject/machine/{id}` - Get machine details
- `GET /dashboard/inject/machine/{mesinId}/checksheets` - Get machine checksheets

## File Structure

```
├── app/
│   ├── Http/Controllers/
│   │   ├── DashboardInjectController.php
│   │   └── AuthController.php
│   └── Models/
│       ├── User.php
│       ├── Mesin.php
│       ├── Part.php
│       ├── Checksheet.php
│       ├── ChecksheetPhoto.php
│       └── Meja.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── layouts/
│       ├── auth/
│       └── controlleader/
├── public/
│   ├── css/
│   └── js/
└── routes/
    └── web.php
```

## Security Features

- **Authentication**: User login/logout system
- **Authorization**: Role-based access control
- **CSRF Protection**: Built-in CSRF token validation
- **File Upload Security**: Validated file types and sizes
- **Input Validation**: Comprehensive form validation

## Browser Compatibility

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers (responsive design)

## Support

For technical support or questions, please contact the development team.

## License

This project is proprietary software for PT Mada Wikri Tunggal Plant 3.
