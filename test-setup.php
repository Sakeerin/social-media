<?php

/**
 * Simple test script to verify the Laravel project setup
 */

echo "Testing Laravel Social Media Project Setup...\n\n";

// Test 1: Check if required files exist
echo "1. Checking required files...\n";
$requiredFiles = [
    'composer.json',
    'package.json',
    'artisan',
    'database/database.sqlite',
    'app/Models/User.php',
    'app/Models/Post.php',
    'app/Models/Comment.php',
    'app/Models/Like.php',
    'app/Models/Follow.php',
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "   ✓ $file exists\n";
    } else {
        echo "   ✗ $file missing\n";
    }
}

echo "\n2. Checking database connection...\n";
try {
    // Simple database test
    if (file_exists('database/database.sqlite')) {
        $pdo = new PDO('sqlite:database/database.sqlite');
        echo "   ✓ Database file exists and is accessible\n";
        
        // Check if tables exist
        $tables = ['users', 'posts', 'comments', 'likes', 'follows'];
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
            $stmt->execute([$table]);
            if ($stmt->fetch()) {
                echo "   ✓ Table '$table' exists\n";
            } else {
                echo "   ✗ Table '$table' missing\n";
            }
        }
    } else {
        echo "   ✗ Database file not found\n";
    }
} catch (Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n3. Checking environment configuration...\n";
if (file_exists('.env')) {
    echo "   ✓ .env file exists\n";
    $envContent = file_get_contents('.env');
    if (strpos($envContent, 'APP_KEY=') !== false && strlen(trim(explode('APP_KEY=', $envContent)[1])) > 0) {
        echo "   ✓ APP_KEY is set\n";
    } else {
        echo "   ✗ APP_KEY is not set\n";
    }
} else {
    echo "   ✗ .env file missing\n";
}

echo "\n4. Testing sample data...\n";
try {
    if (file_exists('database/database.sqlite')) {
        $pdo = new PDO('sqlite:database/database.sqlite');
        
        // Check users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $userCount = $stmt->fetch()['count'];
        echo "   ✓ Users in database: $userCount\n";
        
        // Check posts
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM posts");
        $postCount = $stmt->fetch()['count'];
        echo "   ✓ Posts in database: $postCount\n";
        
        // Check comments
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM comments");
        $commentCount = $stmt->fetch()['count'];
        echo "   ✓ Comments in database: $commentCount\n";
        
        // Check likes
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM likes");
        $likeCount = $stmt->fetch()['count'];
        echo "   ✓ Likes in database: $likeCount\n";
        
        // Check follows
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM follows");
        $followCount = $stmt->fetch()['count'];
        echo "   ✓ Follows in database: $followCount\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error checking sample data: " . $e->getMessage() . "\n";
}

echo "\n5. Checking frontend build...\n";
if (file_exists('public/build')) {
    echo "   ✓ Frontend build directory exists\n";
} else {
    echo "   ✗ Frontend build directory missing (run 'npm run build')\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Setup test completed!\n";
echo "If you see any ✗ marks above, please address those issues.\n";
echo "To start the development server, run: php artisan serve\n";
echo str_repeat("=", 50) . "\n";
