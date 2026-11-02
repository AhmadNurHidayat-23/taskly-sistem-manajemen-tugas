# Taskly

A quiet workspace for getting things done. Taskly is a lightweight, single-user task manager built with Laravel, Blade, and SQLite. Its minimal interface takes inspiration from Swiss editorial design: clear type, generous spacing, subtle borders, and a restrained palette.

## Features

- Create, view, edit, and delete tasks with a confirmation dialog.
- Switch between To Do, In Progress, and Completed; complete or reopen tasks directly from the list.
- Add optional descriptions and due dates, with Low, Medium, or High priority.
- See workspace totals that remain accurate while filtering.
- Search task titles and combine status and priority filters.
- View overdue indicators, deadline ordering, and paginated results.
- Use responsive desktop, tablet, and mobile layouts, with accessible labels and keyboard navigation.
- Get server-side validation, CSRF protection, escaped output, and success messages.

## Stack

- Laravel 13 / PHP 8.3+ (developed and tested with PHP 8.5)
- Blade, HTML, CSS, and vanilla JavaScript
- SQLite with PDO SQLite enabled
- Vite and locally served Inter fonts
- PHPUnit for automated feature tests; Laravel Pint for PHP formatting

## Installation

Requirements: PHP 8.3+, Composer, Node.js 22.12+ (or 20.19+), npm, and the PHP extensions required by Laravel, including `pdo_sqlite` and `sqlite3`.

```sh
git clone https://github.com/AhmadNurHidayat-23/taskly.git
cd taskly
composer run setup
php artisan serve --host=127.0.0.1
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000). The setup command installs dependencies, creates `.env`, generates the application key, runs migrations, and builds assets. Internet access is needed during installation and the initial font build; the built application serves its fonts locally.

For an existing checkout with dependencies installed, run `php artisan serve --host=127.0.0.1`. You do not need MySQL or a running MAMP database.

## Database

The default configuration uses `DB_CONNECTION=sqlite` and `database/database.sqlite`. Laravel creates the SQLite file during interactive migration; the setup command also creates it when missing. To prepare it manually:

```sh
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
```

Optional demo data (six example tasks, safe to run again):

```sh
php artisan db:seed
```

The `tasks` table stores title, description, due date, priority, status, and timestamps. Existing Laravel infrastructure migrations are retained. Local databases and `.env` are excluded from Git.

## Development and checks

```sh
# Run in a second terminal for asset hot reload
npm run dev

# Production asset build
npm run build

# Tests use a separate in-memory SQLite database
composer test

# Format PHP
vendor/bin/pint
```

Feature tests cover CRUD, status changes, optional fields, validation, search, combined filters, dashboard statistics, overdue dates, deadline ordering, pagination, empty states, missing tasks, and HTML escaping.

## Project structure

```text
app/Http/Controllers/TaskController.php   Task actions and list queries
app/Http/Requests/TaskRequest.php         Shared create/edit validation
app/Models/Task.php                      Task data and overdue logic
database/migrations/                    Schema, including tasks
database/factories/TaskFactory.php       Test data
database/seeders/TaskSeeder.php          Optional demo tasks
resources/views/tasks/                  List, form, and detail screens
resources/views/components/             Shared icons and task rows
resources/css/                          Application styles
resources/js/app.js                     Menu, notices, delete confirmation
routes/web.php                          Named web routes
tests/Feature/TaskManagementTest.php     Feature coverage
```

Taskly is a local single-user demo without authentication. Keep the server bound to loopback. Add access protection before hosting the application publicly. Publishing this source repository does not deploy the application.

Built with [Laravel](https://laravel.com/docs/13.x). Laravel is licensed under the MIT license.
