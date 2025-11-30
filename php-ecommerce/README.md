# PHP E-commerce Store

A professional e-commerce platform built with PHP 8.4.11 and MariaDB, following clean architecture principles for high performance and security.

## Tech Stack

- **Backend**: PHP 8.4.11 (Vanilla PHP)
- **Database**: MariaDB 11.8+
- **Frontend**: HTML5, Tailwind CSS, TypeScript
- **Tools**: Composer, Apache/Nginx, Git

## Features

- Product listing and management
- Shopping cart functionality
- Secure checkout flow
- Responsive UI design
- High security standards (CSRF protection, SQL injection prevention)

## Setup

1. Clone the repository
2. Run `composer install` to install PHP dependencies
3. Update database configuration in `app/config/database.php`
4. Run database migrations (instructions in database/migrations/)
5. Set up your web server to point to the `public/` directory
6. Run `npm install` and `npm run build` to build frontend assets

## Directory Structure

```
php-ecommerce/
├── app/
│   ├── config/
│   ├── controllers/
│   ├── models/
│   ├── utils/
│   └── middleware/
├── public/
├── views/
├── database/
├── package.json
├── tailwind.config.js
├── tsconfig.json
├── composer.json
└── README.md
```

## Security Considerations

- Input validation and sanitization
- SQL injection protection using prepared statements
- CSRF protection
- Secure session handling
- Password hashing