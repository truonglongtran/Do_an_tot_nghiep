# Do_an_tot_nghiep

This project is a web application with a backend built using **Laravel** (PHP) and a frontend built using **Vue 3** (Node.js). The project is structured into two main directories: `backend` and `frontend`, each containing the respective codebases. The application uses Docker for consistent development and deployment environments.

## Project Structure
- **`backend`**: Contains the Laravel application, including API routes, migrations, and storage for public assets like banners.
- **`frontend`**: Contains the Vue 3 application, built with Node.js and dependencies like `mitt` for event handling.
- **Database**: The backend uses a MySQL database, with migrations managed by Laravel's Artisan commands.
- **Docker**: Both backend and frontend are containerized using Docker for easy setup and deployment.

## Prerequisites
Before setting up the project, ensure you have the following installed:
- **Docker** and **Docker Compose** (for running the backend and frontend containers)
- **Node.js** (v18 or compatible, as specified in the frontend Dockerfile)
- **PHP** (v8.3.6 or compatible, with `pdo_mysql` extension installed)
- **Composer** (for PHP dependency management)
- **MySQL** (or MySQL Workbench for database management)
- **Git** (for version control)
- **VS Code** (optional, for editing code)

## Setup Instructions

### 1. Clone the Repository
Clone the project from GitHub and navigate to the project directory:
```bash
git clone git@github.com:truonglongtran/Do_an_tot_nghiep.git
cd Do_an_tot_nghiep
```

### 2. Backend Setup
The backend is a Laravel application running in a Docker container.

#### a. Navigate to the Backend Directory
```bash
cd backend
```

#### b. Install PHP Dependencies
Ensure Composer is installed, then run:
```bash
composer install
```

#### c. Configure Environment
Copy the `.env.example` file to `.env` and configure your environment variables (e.g., database credentials):
```bash
cp .env.example .env
nano .env
```

Update the following in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

#### d. Set Permissions
Set appropriate permissions for the Laravel storage directory:
```bash
sudo chown -R www-data:www-data storage/app/public
sudo chmod -R 775 storage/app/public
```

#### e. Build and Run Docker Containers
Assuming a `docker-compose.yml` file is present in the project root, build and start the containers:
```bash
docker-compose up -d
```

If you need a `Dockerfile` for the backend, here’s a sample based on common Laravel setups:
```dockerfile
FROM php:8.3-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
```

Save this as `backend/Dockerfile` and ensure your `docker-compose.yml` references it.

#### f. Run Database Migrations and Seeders
Run migrations to set up the database schema and seed initial data:
```bash
docker exec laravel_app php artisan migrate:fresh --seed
```

If you need to refresh specific migrations (e.g., for the `reports` table):
```bash
docker exec laravel_app php artisan migrate:refresh --path=database/migrations/2025_05_06_075713_create_reports_table.php
```

#### g. Start the Laravel Development Server
For local development, start the Laravel server:
```bash
docker exec laravel_app php artisan serve
```

Alternatively, configure a web server (e.g., Nginx or Apache) to serve the Laravel app from `backend/public`.

#### h. Set Up Cron for Scheduled Tasks
The project uses Laravel's task scheduler (e.g., for `php artisan reports:generate`). Edit the crontab:
```bash
crontab -e
```

Add the following line to run the Laravel scheduler every minute:
```bash
* * * * * cd /path/to/Do_an_tot_nghiep/backend && docker exec laravel_app php artisan schedule:run >> /dev/null 2>&1
```

Verify the cron service is running:
```bash
systemctl status cron
```

### 3. Frontend Setup
The frontend is a Vue 3 application running in a Docker container.

#### a. Navigate to the Frontend Directory
```bash
cd ../frontend
```

#### b. Build and Run Docker Container
Use the provided Dockerfile for the frontend:
```dockerfile
FROM node:18

# Create working directory in container
WORKDIR /app

# Copy package.json and package-lock.json into container
COPY package*.json ./

# Install Vue dependencies
RUN npm install

# Copy Vue source code into container
COPY . .

# Expose port for Vue 3 (5173)
EXPOSE 5173

# Command to run Vue 3 (development)
CMD ["npm", "run", "dev"]
```

Save this as `frontend/Dockerfile`. Then, add the frontend service to your `docker-compose.yml`. Here’s a sample `docker-compose.yml` for both services:
```yaml
version: '3'
services:
  laravel_app:
    build:
      context: ./backend
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    volumes:
      - ./backend:/var/www
    depends_on:
      - mysql
    environment:
      - DB_HOST=mysql
      - DB_DATABASE=your_database_name
      - DB_USERNAME=your_database_user
      - DB_PASSWORD=your_database_password

  vue_app:
    build:
      context: ./frontend
      dockerfile: Dockerfile
    ports:
      - "5173:5173"
    volumes:
      - ./frontend:/app
    command: npm run dev

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: your_database_name
      MYSQL_USER: your_database_user
      MYSQL_PASSWORD: your_database_password
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

Save this as `docker-compose.yml` in the project root. Then, run:
```bash
docker-compose up -d
```

#### c. Start the Frontend Development Server
The frontend will be available at `http://localhost:5173` after running the Docker container.

### 4. Database Management
To manage the database, use **MySQL Workbench**:
```bash
mysql-workbench-community
```

Connect to the MySQL database using the credentials specified in the `backend/.env` file (e.g., host `mysql`, port `3306`).

### 5. Version Control
The project is initialized with Git and linked to a remote repository. To push changes:
```bash
git add .
git commit -m "Your commit message"
git push -u origin main
```

If you encounter SSH issues, verify your SSH configuration:
```bash
ls ~/.ssh
nano ~/.ssh/config
```

Ensure the SSH config includes:
```
Host github.com
  HostName github.com
  User git
  IdentityFile ~/.ssh/id_rsa
```

Test the SSH connection:
```bash
ssh -T git@github.com
```

### 6. Running Reports
To manually generate reports (if implemented in the Laravel app):
```bash
docker exec laravel_app php artisan reports:generate
```

### 7. Accessing the Application
- **Backend**: Access the Laravel app at `http://localhost:8000` (or the port specified in `docker-compose.yml`).
- **Frontend**: Access the Vue 3 app at `http://localhost:5173`.
- **Public Storage**: Public assets (e.g., banners) are stored in `backend/storage/app/public`. Ensure the storage is linked:
  ```bash
  docker exec laravel_app php artisan storage:link
  ```

### Troubleshooting
- **PHP Warnings**: If you encounter errors about `pdo_mysql` or `pdo_sqlite`, ensure the `pdo_mysql` extension is installed in the backend Dockerfile or locally:
  ```bash
  sudo apt-get install php8.3-mysql
  ```
- **Docker Issues**: If containers fail, check the logs:
  ```bash
  docker logs laravel_app
  docker logs vue_app
  ```
- **Git Issues**: If `git push` fails, verify the remote URL:
  ```bash
  git remote set-url origin git@github.com:truonglongtran/Do_an_tot_nghiep.git
  ```
- **Markdown Rendering**: If the README displays as plain text on GitHub, ensure the file is named `README.md` (with the `.md` extension) and uses proper Markdown syntax. Verify the file encoding is UTF-8:
  ```bash
  file -i README.md
  ```

## About Laravel
Laravel is a web application framework with expressive, elegant syntax, designed to make development enjoyable and efficient. Key features include:
- [Simple, fast routing engine](https://laravel.com/docs/routing)
- [Powerful dependency injection container](https://laravel.com/docs/container)
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent)
- Database agnostic [schema migrations](https://laravel.com/docs/migrations)
- [Robust background job processing](https://laravel.com/docs/queues)
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting)

<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions">
    <img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
  </a>
</p>

For more details, visit the [Laravel documentation](https://laravel.com/docs) or explore [Laracasts](https://laracasts.com) for video tutorials.

## Contributing
Contributions are welcome! Please review the [Laravel contribution guide](https://laravel.com/docs/contributions) and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities
If you discover a security vulnerability, please email Taylor Otwell at [taylor@laravel.com](mailto:taylor@laravel.com).

## License
The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).