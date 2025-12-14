# Policies Setup Guide

## Overview

CMS-Core v1.4.0+ includes a role-based permission system using Laravel Policies:

- **UserPolicy**: Controls access to user management (admin only)
- **PostPolicy**: Controls access to posts (admin sees all, members see only their own)

## Installation

The policies are automatically published when running:

```bash
php artisan cms-core:install
```

Or for existing installations:

```bash
php artisan cms-core:update --force
```

## Diagnostic

Check if policies are correctly set up:

```bash
php artisan cms-core:diagnose-policies
```

Expected output:
```
✓ AuthServiceProvider is registered in bootstrap/providers.php
✓ PostPolicy.php exists
✓ UserPolicy.php exists
✓ UserPolicy registered
✓ PostPolicy registered
```

## Manual Setup

If policies aren't working after installation:

### 1. Publish Policies

```bash
php artisan vendor:publish --tag=cms-core-policies --force
php artisan vendor:publish --tag=cms-core-providers --force
```

### 2. Register AuthServiceProvider

Edit `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,  // ← Add this line
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\JetstreamServiceProvider::class,
];
```

### 3. Clear Caches

```bash
php artisan optimize:clear
```

### 4. Logout and Login Again

Permissions are checked on each request, so you need to refresh your session.

## Verification

### As Admin (hola@humano.app):
- ✅ Should see "Users" in menu
- ✅ Can create/edit/delete users
- ✅ Sees all posts
- ✅ Can create categories

### As Member:
- ❌ Should NOT see "Users" in menu
- ❌ If accessing `/admin/users` directly → 403 error
- ✅ Only sees their own posts
- ✅ Can create tags but NOT categories

## Troubleshooting

### Member still sees Users menu

1. Run diagnostic:
   ```bash
   php artisan cms-core:diagnose-policies
   ```

2. Check bootstrap/providers.php:
   ```bash
   cat bootstrap/providers.php | grep AuthServiceProvider
   ```

3. If not registered, add it manually (see step 2 above)

4. Clear caches:
   ```bash
   php artisan optimize:clear
   ```

5. Logout and login again

### Policies not being applied

1. Check policy files exist:
   ```bash
   ls -la app/Policies/
   ls -la app/Providers/AuthServiceProvider.php
   ```

2. If missing, publish them:
   ```bash
   php artisan vendor:publish --tag=cms-core-policies --force
   php artisan vendor:publish --tag=cms-core-providers --force
   ```

3. Clear everything:
   ```bash
   php artisan optimize:clear
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

4. Restart server (if using Herd, Cmd+Q and reopen)

## Policy Details

### UserPolicy

**Location**: `app/Policies/UserPolicy.php`

Controls who can manage users:

- `viewAny`: Who can see users list (admin only)
- `view`: Who can view user details (admin only)
- `create`: Who can create users (admin only)
- `update`: Who can edit users (admin only)
- `delete`: Who can delete users (admin only)

### PostPolicy

**Location**: `app/Policies/PostPolicy.php`

Controls post access:

- `viewAny`: Admin sees all posts, members see only their own
- `create`: Everyone can create posts
- `update`: Admin can edit any post, members only their own
- `delete`: Admin can delete any post, members only their own

## Custom Policies

To create your own policies for new resources:

1. Create policy:
   ```bash
   php artisan make:policy YourModelPolicy --model=YourModel
   ```

2. Register in `app/Providers/AuthServiceProvider.php`:
   ```php
   protected $policies = [
       YourModel::class => YourModelPolicy::class,
   ];
   ```

3. Implement authorization logic following the pattern in UserPolicy/PostPolicy

4. Clear caches and test
