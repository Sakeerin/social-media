#!/bin/bash

echo "Setting up Social Media Laravel Project..."
echo

echo "Step 1: Installing PHP dependencies..."
composer install
if [ $? -ne 0 ]; then
    echo "Error installing PHP dependencies!"
    exit 1
fi

echo
echo "Step 2: Installing Node.js dependencies..."
npm install
if [ $? -ne 0 ]; then
    echo "Error installing Node.js dependencies!"
    exit 1
fi

echo
echo "Step 3: Creating .env file..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo ".env file created from .env.example"
else
    echo ".env file already exists"
fi

echo
echo "Step 4: Generating application key..."
php artisan key:generate
if [ $? -ne 0 ]; then
    echo "Error generating application key!"
    exit 1
fi

echo
echo "Step 5: Creating database file..."
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo "SQLite database file created"
else
    echo "Database file already exists"
fi

echo
echo "Step 6: Running database migrations..."
php artisan migrate
if [ $? -ne 0 ]; then
    echo "Error running migrations!"
    exit 1
fi

echo
echo "Step 7: Seeding database with sample data..."
php artisan db:seed
if [ $? -ne 0 ]; then
    echo "Error seeding database!"
    exit 1
fi

echo
echo "Step 8: Building frontend assets..."
npm run build
if [ $? -ne 0 ]; then
    echo "Error building frontend assets!"
    exit 1
fi

echo
echo "========================================"
echo "Setup completed successfully!"
echo "========================================"
echo
echo "You can now start the development server with:"
echo "php artisan serve"
echo
echo "The application will be available at: http://localhost:8000"
echo
echo "Test users created:"
echo "- john@example.com (password: password)"
echo "- jane@example.com (password: password)"
echo "- mike@example.com (password: password)"
echo "- sarah@example.com (password: password)"
echo "- alex@example.com (password: password)"
echo
