# Detection Engineering Dashboard

Tugas ALP Mata Kuliah Web Programming — Prototype dashboard for managing SIEM detection rules across multiple customers and platforms.

## Tech Stack

- PHP 8.3 
- MySQL 8.0
- CSS 
- Vanilla JavaScript
- Fonts: Plus Jakarta Sans + JetBrains Mono (Google Fonts)

## Directory Structure

```
de-dashboard/
├── index.html              
├── index.php               
├── docs.html               
├── db_config.example.php   
├── .htaccess               
├── .user.ini               
├── css/style.css
├── js/app.js
├── includes/
│   ├── functions.php       
│   └── header.php          
└── pages/
    ├── dashboard.php
    ├── rules.php
    ├── rule_form.php
    ├── settings.php
    ├── settings_siem.php
    └── settings_customer.php
```

## Local Setup

1. Install LAMP stack (Apache, MySQL, PHP 8.3+)
2. Create database: `detection_rule_manager`
3. Import schema via InfinityFree phpMyAdmin or local MySQL client
4. Copy `db_config.example.php` → `db_config.php` and fill credentials
5. Access: `http://localhost/detection-engineering-dashboard/`
