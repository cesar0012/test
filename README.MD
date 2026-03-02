# Track Management System
Developed by: Cesar Edmundo Gutiérrez Romo

Fullstack application for "Synchtank Senior Full Stack Developer - PHP/MS"

## System Requirements
To run this project locally, ensure you have the following installed:
- PHP 8.1 or higher
- Composer
- Node.js and npm
- MySQL or MariaDB server

## Backend Configuration
1. Navigate to the `backend/` directory.
2. Install dependencies:
   `composer install`
3. Configure your database connection in the `.env` file. Ensure the `DATABASE_URL` variable correctly matches your local environment.
4. Prepare the database and tables by running:
   `php bin/console doctrine:database:create`
   `php bin/console doctrine:migrations:migrate`
5. Start the Symfony development server:
   `php -S localhost:8000 -t public`

The backend will be available at `http://localhost:8000`.

## Frontend Configuration
1. Navigate to the `frontend/` directory.
2. Install the necessary packages:
   `npm install`
3. Start the Vite development environment:
   `npm run dev`

The application will default to `http://localhost:5173`.
