# CMS-Core

[![Latest Version on Packagist](https://img.shields.io/packagist/v/idoneo/cms-core.svg?style=flat-square)](https://packagist.org/packages/idoneo/cms-core)
[![Total Downloads](https://img.shields.io/packagist/dt/idoneo/cms-core.svg?style=flat-square)](https://packagist.org/packages/idoneo/cms-core)
[![License](https://img.shields.io/packagist/l/idoneo/cms-core.svg?style=flat-square)](https://packagist.org/packages/idoneo/cms-core)

Multi-tenant CMS foundation with Teams support, Livewire components and Filament integration for Laravel.

## Features

- **BelongsToCurrentTeam** trait for automatic team scoping
- **TeamSwitcher** Livewire component with alphabetical sorting
- **CmsCorePlugin** for Filament panels with user menu
- Toggle teams feature via environment variable

## Installation

### Quick Setup (Recommended)

Install the package via composer (this also installs Jetstream, Filament, Livewire, and Spatie Permission as dependencies):

```bash
composer require idoneo/cms-core
```

Run the installation command:

```bash
php artisan cms-core:install --fresh --seed
```

This will:
- Configure Jetstream with Livewire + Teams
- Publish config files
- Publish and run migrations
- Install Filament panel
- Create admin user (hola@humano.app / Simplicity!)

Build assets:

```bash
npm install && npm run build
```

### Manual Setup

If you need more control:

```bash
# Install dependencies
composer require idoneo/cms-core

# Install Jetstream manually
php artisan jetstream:install livewire --teams

# Publish CMS-Core assets
php artisan vendor:publish --tag="cms-core"

# Run migrations
php artisan migrate

# Build assets
npm install && npm run build
```

Register the plugin in your `AdminPanelProvider`:

```php
use Idoneo\CmsCore\Filament\CmsCorePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(CmsCorePlugin::make())
        // ... other configuration
}
```

### Default Credentials

After running with `--seed` flag:

| Field | Value |
|-------|-------|
| Email | `hola@humano.app` |
| Password | `Simplicity!` |

## Configuration

Add to your `.env` file:

```env
# Enable teams feature (default: false)
APP_TEAMS=true
```

## Usage

### BelongsToCurrentTeam Trait

Add to models that should be scoped by team:

```php
use Idoneo\CmsCore\Traits\BelongsToCurrentTeam;

class Project extends Model
{
    use BelongsToCurrentTeam;
}
```

This automatically:
- Filters queries by `current_team_id`
- Sets `team_id` on create
- Provides `forTeam()` and `withoutTeamScope()` methods

### TeamSwitcher Component

Include in your Blade views:

```blade
@if(\Idoneo\CmsCore\CmsCore::teamsEnabled())
    <livewire:cms-core::team-switcher />
@endif
```

### Filament Plugin

Register in your Panel Provider:

```php
use Idoneo\CmsCore\Filament\CmsCorePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            CmsCorePlugin::make(),
        ]);
}
```

### Helper Methods

```php
use Idoneo\CmsCore\CmsCore;

// Check if teams are enabled
CmsCore::teamsEnabled();

// Get configured models
CmsCore::teamModel();
CmsCore::userModel();
```

### Panel Access Control

By default, all authenticated users can access the Filament panel. To restrict access, implement `FilamentUser` in your User model:

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        // Allow all authenticated users (default behavior)
        return true;
        
        // Or restrict by email domain
        // return str_ends_with($this->email, '@yourdomain.com');
        
        // Or use roles with Spatie Permission
        // return $this->hasRole('admin');
    }
}
```

## Requirements

- PHP ^8.2
- Laravel ^11.0 | ^12.0
- Livewire ^3.0
- Laravel Jetstream with Teams (recommended)

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to Diego Mascarenhas Goytía via [diego.mascarenhas@icloud.com](mailto:diego.mascarenhas@icloud.com). All security vulnerabilities will be promptly addressed.

## License

Licensed under the [GNU Affero General Public License v3.0 (AGPL-3.0)](https://www.gnu.org/licenses/agpl-3.0.html).

### Additional Terms

By deploying this software, you agree to notify the original author at [diego.mascarenhas@icloud.com](mailto:diego.mascarenhas@icloud.com) or by visiting [linkedin.com/in/diego-mascarenhas](https://linkedin.com/in/diego-mascarenhas/). Any modifications or enhancements must be shared with the original author.
