# CMS-Core

[![Latest Version on Packagist](https://img.shields.io/packagist/v/idoneo/cms-core.svg?style=flat-square)](https://packagist.org/packages/idoneo/cms-core)
[![Total Downloads](https://img.shields.io/packagist/dt/idoneo/cms-core.svg?style=flat-square)](https://packagist.org/packages/idoneo/cms-core)
[![License](https://img.shields.io/packagist/l/idoneo/cms-core.svg?style=flat-square)](https://packagist.org/packages/idoneo/cms-core)

Multi-tenant CMS foundation with Teams support, Livewire components and Filament integration for Laravel.

## Features

- **BelongsToCurrentTeam** trait for automatic team scoping
- **TeamSwitcher** Livewire component with alphabetical sorting
- **CmsCorePlugin** for Filament panels with user menu
- **Spatie Laravel Tags** integration for tagging and categorization
- Toggle teams feature via environment variable

## Installation

### Create New Project

To create a new Laravel project with CMS-Core from scratch:

```bash
composer create-project laravel/laravel my-project
cd my-project
composer require idoneo/cms-core
php artisan cms-core:install --fresh --seed
npm install && npm run build
```

This will:
- Create a new Laravel project
- Install CMS-Core package (with Jetstream, Filament, Livewire, and Spatie Permission)
- Configure Jetstream with Livewire + Teams
- Install Filament panel (with Spanish locale)
- Publish Spatie Permission migrations
- Publish CMS-Core config files
- Configure authentication to use Filament login
- Set application locale to Spanish
- Run all migrations
- Create admin user (hola@humano.app / Simplicity!)
- Install and build npm assets

### Install in Existing Project

If you already have a Laravel project, install the package:

```bash
composer require idoneo/cms-core
php artisan cms-core:install --fresh --seed
npm install && npm run build
```

**Note:** The `--fresh` flag will drop all existing tables. If you want to keep your data, use:

```bash
php artisan cms-core:install --seed
php artisan migrate
```

### Update Existing Installation

To update CMS-Core resources (migrations, config, views, translations) in an existing project:

```bash
composer update idoneo/cms-core
php artisan cms-core:update
php artisan migrate
npm run build
```

**Options:**
- `--migrations`: Only publish migrations
- `--force`: Force publish even if files exist

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
```

## Tags System

This package includes Spatie Laravel Tags for tagging and categorization. See [docs/TAGS.md](docs/TAGS.md) for complete documentation.

Quick example:

```php
use Spatie\Tags\HasTags;
use Filament\Forms\Components\SpatieTagsInput;

class Post extends Model
{
    use HasTags;
}

// In Filament Resource
SpatieTagsInput::make('tags')
    ->label('Tags')

// For categorization
SpatieTagsInput::make('categories')
    ->type('categories')
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
# Show/hide teams UI features (default: false)
# Note: Users always have a personal team (Jetstream requirement)
# This setting only controls UI visibility (team switcher, create team, team settings)
APP_TEAMS=false
```

When `APP_TEAMS=false`:
- ✅ Users still have a personal team (required by Jetstream)
- ❌ Team switcher is hidden
- ❌ "Create New Team" option is hidden
- ❌ "Team Settings" option is hidden

When `APP_TEAMS=true`:
- ✅ All team features visible
- ✅ Users can create multiple teams
- ✅ Team switcher appears in user menu

### Brand Logos

CMS-Core automatically configures brand logos for your Filament panel if you place logo files in `public/custom/`:

**Supported files:**
- `logo-light.svg` - Logo for light mode
- `logo-dark.svg` - Logo for dark mode

**Setup:**
1. Create the `public/custom/` directory (if it doesn't exist)
2. Place your logo files:
   ```
   public/
   └── custom/
       ├── logo-light.svg
       └── logo-dark.svg
   ```
3. The logos will be automatically detected and configured on the next page load

**Note:** Only SVG files are supported. The plugin automatically detects and configures the logos without any additional configuration needed.

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

### Team Roles Configuration

After installation, configure team roles in `app/Providers/JetstreamServiceProvider.php`:

```php
protected function configurePermissions(): void
{
    Jetstream::defaultApiTokenPermissions(['read']);

    Jetstream::role('admin', 'Administrator', [
        'create',
        'read',
        'update',
        'delete',
    ])->description('Administrator users can perform any action.');

    Jetstream::role('member', 'Member', [
        'read',
        'create',
        'update',
    ])->description('Members have standard access to create and manage content.');

    Jetstream::role('viewer', 'Viewer', [
        'read',
    ])->description('Viewers can only read and view content.');
}
```

These are **suggested roles** that work for most use cases. You can customize them:
- Rename roles (e.g., "member" → "employee", "student", "collaborator")
- Add new roles
- Modify permissions per role

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

## API Documentation

CMS-Core provides a RESTful API for accessing posts with authentication via Sanctum tokens.

### Authentication

All API endpoints require authentication using a Bearer token. You can generate a token in two ways:

#### Option 1: Generate Token via Artisan Command (Recommended)

Generate a token and add it to your `.env` file:

```bash
# Generate token for admin user (default)
php artisan cms-core:api-token

# Or specify a user email
php artisan cms-core:api-token --email=hola@humano.app --name="API Token"
```

**Output example:**
```
API Token generated successfully!

Token Name: API Token
User: hola@humano.app

Add this to your .env file:
APP_TOKEN=1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz

⚠️  Save this token now! You won't be able to see it again.
```

**Copy the token and add it to your `.env` file:**
```env
APP_TOKEN=1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz
```

**Then use it in your curl requests:**
```bash
# Replace YOUR_TOKEN with the token from .env
curl -X GET "http://localhost:8000/api/posts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

#### Option 2: Generate Token via User Profile

Generate a token in your user profile settings at `/user/api-tokens` or via Jetstream's API token management interface.

**Headers:**
```
Authorization: Bearer {your-token}
Accept: application/json
```

**Note:** If you set `APP_TOKEN` in your `.env`, you can use that token directly. The system will automatically authenticate the user associated with that token.

#### Quick Start Example

1. **Generate a token:**
```bash
php artisan api:token --email=hola@humano.app
```

2. **Copy the output token to your `.env` file:**
```env
APP_TOKEN=1|abc123def456ghi789...
```

3. **Test the API with curl:**
```bash
# List all published posts
curl -X GET "http://localhost:8000/api/posts" \
  -H "Authorization: Bearer 1|abc123def456ghi789..." \
  -H "Accept: application/json"

# Or use the token from .env (if you have it exported)
curl -X GET "http://localhost:8000/api/posts" \
  -H "Authorization: Bearer $APP_TOKEN" \
  -H "Accept: application/json"
```

### Endpoints

#### List Posts

Get a paginated list of posts with optional filters.

**GET** `/api/posts`

**Query Parameters:**
- `status` (optional): Filter by status (`draft`, `published`, `archived`). Default: `published`
- `category` (optional): Filter by category name
- `tag` (optional): Filter by tag name
- `search` (optional): Search in title, excerpt, and content
- `per_page` (optional): Number of items per page (1-100, default: 15)
- `page` (optional): Page number (default: 1)

**Example Request:**
```bash
# Using token from .env (APP_TOKEN)
curl -X GET "http://localhost:8000/api/posts?status=published&category=Tutorials&per_page=10" \
  -H "Authorization: Bearer $(php artisan tinker --execute='echo env(\"APP_TOKEN\");')" \
  -H "Accept: application/json"

# Or using a token directly
curl -X GET "http://localhost:8000/api/posts?status=published&category=Tutorials&per_page=10" \
  -H "Authorization: Bearer 1|your-token-here" \
  -H "Accept: application/json"

# Simple example (replace YOUR_TOKEN with your actual token)
curl -X GET "http://localhost:8000/api/posts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "My First Post",
      "slug": "my-first-post",
      "excerpt": "This is an excerpt...",
      "content": "Full content here...",
      "status": "published",
      "published_at": "2024-01-15T10:00:00Z",
      "created_at": "2024-01-15T09:00:00Z",
      "updated_at": "2024-01-15T09:30:00Z",
      "author": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "featured_image": {
        "url": "https://...",
        "thumb": "https://...",
        "web": "https://..."
      },
      "gallery": [
        {
          "url": "https://...",
          "thumb": "https://...",
          "web": "https://..."
        }
      ],
      "categories": [
        {
          "id": 1,
          "name": "Tutorials",
          "slug": "tutorials"
        }
      ],
      "tags": [
        {
          "id": 2,
          "name": "Laravel",
          "slug": "laravel"
        }
      ]
    }
  ],
  "links": {
    "first": "https://...",
    "last": "https://...",
    "prev": null,
    "next": "https://..."
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 10,
    "to": 10,
    "total": 50
  }
}
```

#### Get Post by Slug

Get a single post by its slug.

**GET** `/api/posts/{slug}`

**Example Request:**
```bash
# Using token from .env (APP_TOKEN)
curl -X GET "http://localhost:8000/api/posts/my-first-post" \
  -H "Authorization: Bearer $(php artisan tinker --execute='echo env(\"APP_TOKEN\");')" \
  -H "Accept: application/json"

# Or using a token directly
curl -X GET "http://localhost:8000/api/posts/my-first-post" \
  -H "Authorization: Bearer 1|your-token-here" \
  -H "Accept: application/json"

# Simple example (replace YOUR_TOKEN with your actual token)
curl -X GET "http://localhost:8000/api/posts/my-first-post" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Example Response:**
```json
{
  "data": {
    "id": 1,
    "title": "My First Post",
    "slug": "my-first-post",
    "excerpt": "This is an excerpt...",
    "content": "Full content here...",
    "status": "published",
    "published_at": "2024-01-15T10:00:00Z",
    "created_at": "2024-01-15T09:00:00Z",
    "updated_at": "2024-01-15T09:30:00Z",
    "author": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "featured_image": {
      "url": "https://...",
      "thumb": "https://...",
      "web": "https://..."
    },
    "gallery": [],
    "categories": [],
    "tags": []
  }
}
```

### Team Scoping

If teams are enabled (`APP_TEAMS=true`), the API automatically filters posts by the authenticated user's current team. Users can only access posts from their current team.

### Error Responses

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated."
}
```

**404 Not Found:**
```json
{
  "message": "No query results for model [App\\Models\\Post] {slug}"
}
```

**422 Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "status": ["The selected status is invalid."]
  }
}
```

### Complete cURL Examples

Here are ready-to-use curl examples (replace `YOUR_TOKEN` with your actual token):

**List all published posts:**
```bash
curl -X GET "http://localhost:8000/api/posts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**List posts with filters:**
```bash
curl -X GET "http://localhost:8000/api/posts?status=published&category=Tutorials&search=laravel&per_page=5" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Get a specific post by slug:**
```bash
curl -X GET "http://localhost:8000/api/posts/my-first-post" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Filter by tag:**
```bash
curl -X GET "http://localhost:8000/api/posts?tag=laravel" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Pretty print JSON response (using jq):**
```bash
curl -X GET "http://localhost:8000/api/posts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | jq
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
