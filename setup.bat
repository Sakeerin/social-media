@echo off
echo Setting up Social Media Laravel Project...
echo.

echo Step 1: Installing PHP dependencies...
composer install
if %errorlevel% neq 0 (
    echo Error installing PHP dependencies!
    pause
    exit /b 1
)

echo.
echo Step 2: Installing Node.js dependencies...
npm install
if %errorlevel% neq 0 (
    echo Error installing Node.js dependencies!
    pause
    exit /b 1
)

echo.
echo Step 3: Creating .env file...
if not exist .env (
    copy .env.example .env
    echo .env file created from .env.example
) else (
    echo .env file already exists
)

echo.
echo Step 4: Generating application key...
php artisan key:generate
if %errorlevel% neq 0 (
    echo Error generating application key!
    pause
    exit /b 1
)

echo.
echo Step 5: Creating database file...
if not exist database\database.sqlite (
    type nul > database\database.sqlite
    echo SQLite database file created
) else (
    echo Database file already exists
)

echo.
echo Step 6: Running database migrations...
php artisan migrate
if %errorlevel% neq 0 (
    echo Error running migrations!
    pause
    exit /b 1
)

echo.
echo Step 7: Seeding database with sample data...
php artisan db:seed
if %errorlevel% neq 0 (
    echo Error seeding database!
    pause
    exit /b 1
)

echo.
echo Step 8: Building frontend assets...
npm run build
if %errorlevel% neq 0 (
    echo Error building frontend assets!
    pause
    exit /b 1
)

echo.
echo ========================================
echo Setup completed successfully!
echo ========================================
echo.
echo You can now start the development server with:
echo php artisan serve
echo.
echo The application will be available at: http://localhost:8000
echo.
echo Test users created:
echo - john@example.com (password: password)
echo - jane@example.com (password: password)
echo - mike@example.com (password: password)
echo - sarah@example.com (password: password)
echo - alex@example.com (password: password)
echo.
pause
