# Social Media Laravel Application

A modern social media application built with Laravel 11 and React, featuring user authentication, posts, comments, likes, and following system.

## Features

- **User Authentication**: Email/password and Google OAuth integration
- **Posts**: Create, view, and manage social media posts with images
- **Comments**: Add comments to posts
- **Likes**: Like and unlike posts
- **Following System**: Follow and unfollow other users
- **User Profiles**: User profiles with avatars and descriptions
- **Modern UI**: Built with React and Tailwind CSS

## Tech Stack

### Backend
- Laravel 11
- PHP 8.2+
- SQLite (default) / MySQL / PostgreSQL
- Laravel Sanctum (API authentication)
- Laravel Socialite (OAuth)

### Frontend
- React 19
- TypeScript
- Tailwind CSS
- Vite (build tool)
- Wouter (routing)

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- SQLite (or MySQL/PostgreSQL if preferred)

## Quick Setup

### Windows
Run the setup script:
```bash
setup.bat
```

### Linux/macOS
Make the script executable and run:
```bash
chmod +x setup.sh
./setup.sh
```

## Manual Setup

If you prefer to set up manually or the scripts don't work:

### 1. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Setup
```bash
# Create SQLite database file (if using SQLite)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

### 4. Build Frontend Assets
```bash
# Build for production
npm run build

# Or run in development mode
npm run dev
```

### 5. Start the Server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Sample Data

The seeder creates the following test users:
- **john@example.com** (password: password) - Software developer
- **jane@example.com** (password: password) - Photographer
- **mike@example.com** (password: password) - Fitness trainer
- **sarah@example.com** (password: password) - Artist
- **alex@example.com** (password: password) - Entrepreneur

Plus 15 additional random users with posts, comments, likes, and follows.

## Database Structure

### Users Table
- id (UUID)
- name
- email
- password
- description
- auth_type (password/google)
- avatar
- email_verified_at
- timestamps

### Posts Table
- id (UUID)
- user_id (foreign key)
- caption
- image
- timestamps

### Comments Table
- id (UUID)
- post_id (foreign key)
- user_id (foreign key)
- content
- timestamps

### Likes Table
- id (UUID)
- post_id (foreign key)
- user_id (foreign key)
- timestamps

### Follows Table
- id (UUID)
- from_id (foreign key to users)
- to_id (foreign key to users)
- timestamps

## API Endpoints

The application provides a RESTful API with the following main endpoints:

### Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get current user

### Posts
- `GET /api/posts` - Get all posts
- `POST /api/posts` - Create a new post
- `GET /api/posts/{id}` - Get specific post
- `PUT /api/posts/{id}` - Update post
- `DELETE /api/posts/{id}` - Delete post

### Comments
- `GET /api/posts/{postId}/comments` - Get post comments
- `POST /api/posts/{postId}/comments` - Add comment to post
- `DELETE /api/comments/{id}` - Delete comment

### Likes
- `POST /api/posts/{postId}/like` - Like/unlike post

### Follows
- `POST /api/users/{userId}/follow` - Follow/unfollow user
- `GET /api/users/{userId}/followers` - Get user followers
- `GET /api/users/{userId}/following` - Get users being followed

## Development

### Running in Development Mode
```bash
# Start Laravel server
php artisan serve

# In another terminal, start Vite dev server
npm run dev
```

### Database Commands
```bash
# Reset and seed database
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=UserSeeder

# Create new migration
php artisan make:migration create_table_name

# Create new model with migration
php artisan make:model ModelName -m
```

### Frontend Development
```bash
# Watch for changes and rebuild
npm run dev

# Build for production
npm run build
```

## Configuration

### Database
Edit `.env` file to change database configuration:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

For MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=social_media
DB_USERNAME=root
DB_PASSWORD=
```

### Google OAuth (Optional)
Add your Google OAuth credentials to `.env`:
```env
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

## Troubleshooting

### Common Issues

1. **Permission denied on setup script**
   ```bash
   chmod +x setup.sh
   ```

2. **Database connection error**
   - Ensure database file exists: `touch database/database.sqlite`
   - Check database configuration in `.env`

3. **Frontend build errors**
   - Clear node_modules: `rm -rf node_modules && npm install`
   - Check Node.js version (requires 18+)

4. **Migration errors**
   - Reset database: `php artisan migrate:fresh --seed`

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and ensure everything works
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).