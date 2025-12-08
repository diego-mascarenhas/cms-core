# CMS-Core

CMS-Core is the foundation for building powerful, multi-tenant content management systems. Designed with simplicity and scalability in mind, it provides a robust starting point for developers who need a flexible, team-based platform for managing digital content and business operations.

## Stack

- **[Laravel 12](https://laravel.com)** - The PHP Framework for Web Artisans
- **[Filament 4](https://filamentphp.com)** - Beautiful admin panels built with TALL stack
- **[Jetstream](https://jetstream.laravel.com)** - Authentication scaffolding with Teams support
- **[Livewire 3](https://livewire.laravel.com)** - Full-stack framework for Laravel
- **[Spatie Permission](https://spatie.be/docs/laravel-permission)** - Role and permission management
- **[Tailwind CSS](https://tailwindcss.com)** - Utility-first CSS framework

## Features

- Multi-tenant architecture with optional Teams support
- Role-based access control (RBAC)
- Modern admin panel powered by Filament
- Two-factor authentication
- API support with Laravel Sanctum
- Legacy database connection support

## Requirements

- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL / MariaDB / SQLite

## Installation

Clone the repository:

```bash
git clone git@github.com:diego-mascarenhas/cms-core.git
cd cms-core
composer install
```

Configure environment variables:

```bash
cp .env.example .env
```

Generate application key and run migrations:

```bash
php artisan key:generate
php artisan migrate --seed
```

Install frontend dependencies and build assets:

```bash
npm install
npm run build
```

## Configuration

### Teams Feature

Teams can be enabled or disabled via environment variable:

```env
# Enable teams (multi-tenant)
APP_TEAMS=true

# Disable teams (single user)
APP_TEAMS=false
```

### Legacy Database Connection

For migrations from legacy systems:

```env
DB_LEGACY_CONNECTION=mysql
DB_LEGACY_HOST=127.0.0.1
DB_LEGACY_PORT=3306
DB_LEGACY_DATABASE=cms_legacy
DB_LEGACY_USERNAME=root
DB_LEGACY_PASSWORD=
```

## Default Credentials

After running seeders:

| Field | Value |
|-------|-------|
| Email | `hola@humano.app` |
| Password | `Simplicity!` |

Access the admin panel at: `/admin`

## Contributing

Thank you for considering contributing to CMS-Core!

## Security Vulnerabilities

If you discover a security vulnerability within CMS-Core, please send an e-mail to Diego Mascarenhas Goytía via [diego.mascarenhas@icloud.com](mailto:diego.mascarenhas@icloud.com). All security vulnerabilities will be promptly addressed.

## License

CMS-Core is open-sourced software licensed under the [GNU Affero General Public License v3.0](https://www.gnu.org/licenses/agpl-3.0.html).

### Additional Terms

By deploying this software, you agree to notify the original author at [diego.mascarenhas@icloud.com](mailto:diego.mascarenhas@icloud.com) or by visiting [linkedin.com/in/diego-mascarenhas](http://linkedin.com/in/diego-mascarenhas/). Any modifications or enhancements must be shared with the original author.
