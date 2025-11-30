# PHP E-commerce Store Setup Instructions

## Database Configuration

To set up the database for the e-commerce application on a Linux system with MariaDB, follow these steps:

### 1. Create Database User (with sudo access)

```bash
sudo mysql -u root -e "CREATE USER 'ecommerce_user'@'localhost' IDENTIFIED BY 'ecommerce_password';"
sudo mysql -u root -e "CREATE DATABASE ecommerce_db;"
sudo mysql -u root -e "GRANT ALL PRIVILEGES ON ecommerce_db.* TO 'ecommerce_user'@'localhost';"
sudo mysql -u root -e "FLUSH PRIVILEGES;"
```

### 2. Update Configuration

The database configuration file is already set up with the correct credentials in `app/config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce_db');
define('DB_USER', 'ecommerce_user');
define('DB_PASS', 'ecommerce_password');
```

### 3. Run Migrations

After setting up the database user and permissions, run the initialization script:

```bash
cd /home/saleh/projects/apps/1/php-ecommerce
php init.php
```

This will create the database tables and populate them with sample data.

### 4. Alternative: Using phpMyAdmin or Adminer

If you prefer a GUI approach to setting up the database:

1. Access phpMyAdmin or Adminer through your web server
2. Create a new database named `ecommerce_db`
3. Create a new user `ecommerce_user` with password `ecommerce_password`
4. Grant all privileges on `ecommerce_db` to `ecommerce_user`
5. Run the schema SQL file: `database/schema.sql`

### 5. Running the Application

Once the database is properly configured, you can run the application using PHP's built-in server:

```bash
cd /home/saleh/projects/apps/1/php-ecommerce/public
php -S localhost:8000
```

Visit `http://localhost:8000` in your browser to access the application.

### 6. Production Setup

For production environments, consider:

1. Using environment variables instead of hardcoded credentials
2. Setting up proper web server (Apache/Nginx) configurations
3. Implementing SSL certificates
4. Optimizing database performance
5. Setting up backup procedures