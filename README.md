# MyBlog: Blog App Development

A full-featured blogging platform built with **Laravel 11**. Writers publish posts, admins review and approve them, and the whole site is managed from an admin panel with roles, permissions and an activity log.

## Features

**Blog**
- Posts with cover image, categories and tags
- Comments and reactions
- Search, category and tag pages
- Dark and light theme, responsive layout
- Built-in SEO fields per post (meta title, description, keywords, image alt text, generated share image)

**Accounts**
- Register and login, sign in with Google
- Password reset with an emailed code
- Profile page (avatar, bio, website)
- Notifications

**Admin panel**
- Overview dashboard
- Post approval workflow (pending, approved, rejected)
- Category and user management
- Roles and permissions: create roles, add, edit and delete permissions, assign them to roles
- Activity log: who did what, when, with filters and old/new values
- SMTP mail settings configurable from the panel, with a styled HTML test email
- API tokens: create and revoke tokens for the REST API, and see recent API requests

**REST API**
- Token login with Laravel Sanctum
- Endpoints for posts and categories (list, view, create, update, delete)
- Every API request is logged (method, path, status, IP, response time)

**Scheduled tasks**
- Weekly summary email to admins (new posts, comments, reactions, users, top posts, posts waiting for approval)

## Tech stack

| Area | Technology |
|---|---|
| Backend | PHP 8.2+, Laravel 11, Laravel Sanctum (API tokens) |
| Frontend | Blade, Bootstrap 5, Bootstrap Icons |
| Database | MySQL / MariaDB |
| Mail | SMTP (configurable) |

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL 5.7+ or MariaDB 10.4+
- A web server (Apache, Nginx, or `php artisan serve` for local use)

## Installation

```bash
# 1. Clone the project
git clone https://github.com/YOUR-USERNAME/YOUR-REPO.git
cd YOUR-REPO

# 2. Install PHP packages
composer install

# 3. Create the environment file and app key
cp .env.example .env
php artisan key:generate
```

Open `.env` and set your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blog
DB_USERNAME=root
DB_PASSWORD=
DB_COLLATION=utf8mb4_unicode_ci
```

Then:

```bash
# 4. Create the tables (this also creates the default roles and permissions)
php artisan migrate

# 5. Optional: demo data and the admin account
php artisan db:seed

# 6. Make uploaded images available
php artisan storage:link

# 7. Start the app
php artisan serve
```

Open <http://127.0.0.1:8000>.

## First admin account

The seeder creates a demo admin (`admin@test.com`). Check `database/seeders` for the demo password, or set your own, and **change it straight after the first login**. Never keep demo credentials on a public site.

## Configuration

### Mail

Either set the `MAIL_*` values in `.env`, or turn on **Custom SMTP** in *Admin > Mail Settings* and use the **Send test email** button to check it.

```env
MAIL_MAILER=smtp
MAIL_HOST=your-mail-server
MAIL_PORT=465
MAIL_USERNAME=you@example.com
MAIL_PASSWORD='your-password'
MAIL_SCHEME=smtps
MAIL_FROM_ADDRESS="you@example.com"
MAIL_FROM_NAME="MyBlog"
```

> Put the password in single quotes if it contains special characters.

### Google login (optional)

Add your Google OAuth credentials in `.env`:

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
```

### Timezone and URL

```env
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Asia/Colombo
```

`APP_URL` is used for the links inside emails, so set it to your real site address on a live server.

## REST API

The API uses **Laravel Sanctum** bearer tokens. All API responses are JSON, so send `Accept: application/json`.

### Get a token

Either log in through the API:

```http
POST /api/login
Content-Type: application/json
Accept: application/json

{"email": "you@example.com", "password": "your-password", "device_name": "my-app"}
```

```json
{"success": true, "token": "1|abc123...", "user": {"id": 1, "name": "...", "email": "..."}}
```

or create one in **Admin > API Tokens** (pick a user, give it a name, copy the token; it is shown only once).

### Use the token

```http
GET /api/posts
Authorization: Bearer 1|abc123...
Accept: application/json
```

### Endpoints

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/login` | Get a token (public, rate limited) |
| POST | `/api/logout` | Revoke the current token |
| GET | `/api/user` | The authenticated user |
| GET | `/api/categories` | List categories |
| GET | `/api/posts` | List posts (paginated). Filters: `category_id`, `q` |
| GET | `/api/posts/{id}` | One post |
| POST | `/api/posts` | Create a post (`category_id`, `title`, `slug`, `body`, optional `excerpt`) |
| PUT / PATCH | `/api/posts/{id}` | Update a post (author or admin only) |
| DELETE | `/api/posts/{id}` | Delete a post (author or admin only) |

Example with curl:

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"you@example.com","password":"your-password"}'

curl http://127.0.0.1:8000/api/posts \
  -H "Accept: application/json" -H "Authorization: Bearer YOUR_TOKEN"
```

Requests and responses are saved in the `api_request_logs` table and shown in *Admin > API Tokens*. Passwords are never stored in the log.

> **Live server note:** the API needs the `personal_access_tokens` and `api_request_logs` tables (`php artisan migrate`).

## Scheduled tasks (cron)

The weekly summary runs every Monday at 08:00. You can also run it by hand:

```bash
php artisan report:weekly --to=you@example.com   # send to one address (test)
php artisan report:weekly                         # send to all admins
php artisan schedule:list                         # see scheduled tasks
```

For the schedule to run automatically, call the scheduler every minute:

- **Linux / cPanel cron:** `* * * * * /usr/local/bin/php /path/to/project/artisan schedule:run >> /dev/null 2>&1`
- **Windows (Laragon):** a Task Scheduler task every minute running `php artisan schedule:run`, or use `php artisan schedule:work` while developing.

## Roles and permissions

Access is controlled by permissions, not by hard-coded roles. Manage them in *Admin > Roles & Permissions* and *Admin > Permissions*.

| Permission | Allows |
|---|---|
| `posts.create` | Write and manage own posts |
| `posts.manage` | View, approve, reject and delete any post |
| `comments.create` | Write comments |
| `comments.delete_any` | Delete other users' comments |
| `reactions.create` | React to posts |
| `admin.access` | Open the admin panel |
| `categories.manage` | Manage categories |
| `users.manage` | Manage users and their roles |
| `roles.manage` | Manage roles and permissions |
| `settings.manage` | Change mail settings |
| `activity.view` | View the activity log |

Admin > API Tokens needs `settings.manage`.

You can add your own permissions in the admin panel and use them in code with `@can('your.slug')` or `middleware('can:your.slug')`. The **Admin** role always has every permission.

## Project structure

```
app/
  Console/Commands/     scheduled commands (weekly summary)
  Http/Controllers/     site and admin controllers
  Http/Controllers/Api/ REST API controllers
  Http/Middleware/      admin check, API request logging
  Mail/                 email classes
  Models/               Eloquent models
  Support/              helpers (SEO, activity log, API logger)
database/migrations/    table definitions
resources/views/        Blade templates (layouts, admin, emails, ...)
public/css, public/js   site styles and scripts
routes/web.php          web routes
routes/api.php          API routes
routes/console.php      scheduled tasks
```

## Deploying to a live server (cPanel)

1. Upload the project files (without `.git`) and set the document root to the `public` folder.
2. Create a MySQL database and user, and fill in the `.env`.
3. Import your database export, or run `php artisan migrate --force`.
4. Set `APP_ENV=production`, `APP_DEBUG=false` and the real `APP_URL`.
5. Add the cron entry for the scheduler.

> **Tip:** if you import a database exported from MySQL 8, replace `utf8mb4_0900_ai_ci` with `utf8mb4_unicode_ci` in the `.sql` file first, because MariaDB and older MySQL do not support it.

## Author

Shehani De Silva
