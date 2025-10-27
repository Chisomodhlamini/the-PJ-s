# My Boarding House Management System

A comprehensive web-based boarding house management system with Admin Dashboard and Boarding House Locator modules.

## 🚀 Features

### Admin Dashboard Module
- **Dashboard Overview**: Real-time statistics, charts, and activity logs
- **Landlord Management**: Complete CRUD operations with verification system
- **Payment Monitoring**: Track payments with filtering and status updates
- **Boarding House Locator**: Interactive map with search functionality
- **System Alerts**: Notifications for pending verifications and overdue payments

### Boarding House Locator Module
- **Interactive Map**: Leaflet.js integration with custom markers
- **Advanced Search**: Filter by location, price range, and landlord
- **Verified Houses Only**: Only shows houses from paid and verified landlords
- **Contact Integration**: Direct contact options for landlords
- **Export Functionality**: CSV export of verified boarding houses

## 🛠️ Tech Stack

- **Backend**: PHP 8+ (Procedural with lightweight MVC)
- **Database**: MySQL with PDO
- **Frontend**: HTML5, CSS3, JavaScript
- **UI Framework**: Bootstrap 5 + Custom Theme
- **Maps**: Leaflet.js
- **Charts**: Chart.js
- **Icons**: Font Awesome 6
- **Font**: Inter

## 🎨 Design Features

- **Professional UI**: Modern cards, subtle shadows, consistent typography
- **Mobile Responsive**: Fully responsive with hamburger navigation
- **Color Palette**: 
  - Primary Blue: #0066ff
  - Secondary Navy: #002147
  - Accent Gold: #f4b400
- **Custom Theme**: Professional admin dashboard styling

## 📁 Project Structure

```
boarding houses project/
├── config/
│   ├── database.php          # Database configuration
│   └── functions.php         # Utility functions
├── src/
│   ├── Controllers/
│   │   └── AdminController.php
│   └── Models/
│       ├── Admin.php
│       ├── Landlord.php
│       ├── BoardingHouse.php
│       └── Payment.php
├── views/admin/
│   ├── assets/
│   │   ├── css/
│   │   │   └── admin.css
│   │   └── js/
│   │       ├── admin.js
│   │       └── locator.js
│   ├── dashboard.php
│   ├── landlords.php
│   ├── payments.php
│   ├── locator.php
│   ├── login.php
│   ├── logout.php
│   └── ajax.php
└── sql/
    └── schema.sql
```

## 🚀 Installation & Setup

### 1. Database Setup

1. Create a MySQL database named `boarding_house_db`
2. Import the schema file:
   ```bash
   mysql -u root -p boarding_house_db < sql/schema.sql
   ```

### 2. Configuration

1. Update database credentials in `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'boarding_house_db';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

### 3. Web Server Setup

1. Place the project files in your web server directory
2. Ensure PHP 8+ is installed
3. Enable PDO MySQL extension

### 4. Default Admin Login

- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@boardinghouse.com`

## 📊 Database Schema

### Key Tables

- **admins**: System administrators
- **landlords**: Property owners with verification status
- **boarding_houses**: Property listings with location data
- **tenants**: Renters and their information
- **payments**: Payment transactions and tracking
- **activity_logs**: System activity tracking
- **system_settings**: Configuration settings

### Key Features

- **Verification System**: Landlords must be verified to appear in locator
- **Payment Tracking**: Monitor subscription payments and rent payments
- **Location Services**: GPS coordinates for map integration
- **Activity Logging**: Track all admin actions

## 🎯 Usage

### Admin Dashboard

1. **Login**: Access admin panel at `/views/admin/login.php`
2. **Dashboard**: View system overview and statistics
3. **Landlord Management**: Verify, suspend, or manage landlords
4. **Payment Monitoring**: Track and update payment statuses
5. **Locator**: View verified boarding houses on interactive map

### Boarding House Locator

1. **Search**: Use filters to find houses by location, price, or landlord
2. **Map View**: Interactive map with custom markers
3. **List View**: Grid layout with house cards
4. **Contact**: Direct contact options for landlords
5. **Export**: Download verified houses data as CSV

## 🔧 Customization

### Adding New Features

1. **Models**: Add new model classes in `src/Models/`
2. **Controllers**: Extend `AdminController` for new functionality
3. **Views**: Create new PHP files in `views/admin/`
4. **JavaScript**: Add functionality in `assets/js/`

### Styling

- **CSS Variables**: Modify colors in `admin.css`
- **Bootstrap**: Override Bootstrap classes as needed
- **Responsive**: Mobile-first design approach

## 🔒 Security Features

- **CSRF Protection**: All forms protected with CSRF tokens
- **Input Sanitization**: All user inputs sanitized
- **Session Management**: Secure admin session handling
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Protection**: HTML entity encoding

## 📱 Mobile Support

- **Responsive Design**: Works on all device sizes
- **Touch-Friendly**: Optimized for mobile interactions
- **Hamburger Menu**: Collapsible navigation for mobile
- **Mobile Maps**: Touch-optimized map interactions

## 🚀 Performance

- **Optimized Queries**: Efficient database queries with indexes
- **Lazy Loading**: Charts and maps load on demand
- **Caching**: Session-based caching for better performance
- **Minified Assets**: Optimized CSS and JavaScript

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection**: Check credentials in `config/database.php`
2. **Permission Errors**: Ensure web server has write permissions
3. **Map Not Loading**: Check internet connection for Leaflet.js
4. **Charts Not Displaying**: Verify Chart.js CDN is accessible

### Debug Mode

Enable error reporting in PHP for development:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📄 License

This project is created for educational and commercial use. Please ensure you have proper licensing for any third-party libraries used.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For support and questions, please contact the development team or create an issue in the project repository.

---

**My Boarding House Management System** - Professional boarding house management made simple.
