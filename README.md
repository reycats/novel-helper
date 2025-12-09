# Novel Text Extractor & Formatter

![Banner](https://via.placeholder.com/1200x400/0a0a0a/d4af37?text=Novel+Text+Extractor+%26+Formatter)

> Professional tool for extracting and formatting novel text from HTML with AI-powered paragraph separation

## 🚀 Live Demo
[Demo Website](https://novel-helper.xo.je/) | [Admin Demo](https://novel-helper.xo.je/admin.php)

## ✨ Features
- **Smart Text Extraction** - Clean novel text from HTML
- **Auto Paragraph Separation** - AI-powered paragraph detection
- **Premium License System** - Controlled access management
- **Modern Dashboard** - Beautiful and intuitive interface
- **Ad Protection** - Anti auto-click technology
- **Responsive Design** - Works on all devices

## 📦 Quick Start

### Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- Composer (optional)

### Installation
1. Clone the repository:
```bash
git clone https://github.com/yourusername/novel-extractor.git
cd novel-extractor
Configure database:

Create MySQL database

Import database.sql (if exists)

Or run SQL from config.example.php

Setup configuration:

bash
cp config.example.php config.php
# Edit config.php with your settings
Upload to server and set permissions:

bash
chmod 755 uploads/ logs/
Access the application:

text
https://your-domain.com/
🔧 Configuration
Basic Settings
Edit config.php:

php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'novel_extractor');
define('DB_USER', 'username');
define('DB_PASS', 'password');

// Application Settings
define('APP_NAME', 'Novel Extractor Pro');
define('BASE_URL', 'https://your-domain.com/');

// License Settings
define('ADMIN_LICENSE', 'RM0991081225');
Ad Configuration
Edit ad settings in index.php:

javascript
atOptions = {
    'key' : 'your_adsterra_key',
    'format' : 'iframe',
    'height' : 250,
    'width' : 300
};
🎮 Usage Guide
For Users
Get License Key from administrator

Login with your license key

Paste HTML novel content

Click Extract to get clean text

Copy/Download the result

For Administrators
Login with admin license: RM0991081225

Access admin dashboard

Manage user licenses

View usage statistics

Configure system settings

📁 Project Structure (ga lengkap structurenya)
text
novel-extractor/
├── index.php          # Main login page
├── dashboard.php      # User dashboard
├── config.php         # Configuration file
├── uploads/          # User uploads
├── logs/            # System logs
└── README.md        # This file
🛡️ Security Features
License key validation

Session-based authentication

SQL injection prevention

XSS protection

Ad click protection

Activity logging

📱 Mobile Support
Responsive design

Touch-friendly interface

Mobile-optimized ads

Fast loading speed

🤝 Contributing
Contributions are welcome! Please read our Contributing Guidelines first.

Fork the repository

Create your feature branch (git checkout -b feature/AmazingFeature)

Commit your changes (git commit -m 'Add some AmazingFeature')

Push to the branch (git push origin feature/AmazingFeature)

Open a Pull Request

🐛 Troubleshooting
Common issues and solutions:

Issue	Solution
License invalid	Check license key validity
Database error	Verify database credentials
Ad not showing	Disable ad blockers
Slow extraction	Check PHP memory limit
📄 License
This project is licensed under the Custom License - see the LICENSE file for details.

⚠️ Important: Commercial use and redistribution are prohibited.

📞 Support
Issues: GitHub Issues

Email: reynotdeveloper@gmail.com

Documentation: Wiki
