# CMS-Core Update Guide

This guide explains how to update existing CMS-Core installations to get the latest features, including the new role-based permissions system.

## Update Process

### Step 1: Update the Package

Update the CMS-Core package to the latest version:

```bash
composer update idoneo/cms-core
```

### Step 2: Run the Update Command

Run the CMS-Core update command to publish new files:

```bash
php artisan cms-core:update --force
```

This command will:
- ✅ Publish new policies (`PostPolicy.php`, `UserPolicy.php`)
- ✅ Publish `AuthServiceProvider.php`
- ✅ Register `AuthServiceProvider` in `bootstrap/providers.php`
- ✅ Update models, views, and other resources
- ✅ Clean up duplicate migrations

### Step 3: Update User Roles (Manual)

For existing installations, you need to manually update user roles in the database.

#### Option A: Using SQL

Connect to your database and run:

```sql
-- View current users and their roles
SELECT u.id, u.name, u.email, tu.role, tu.team_id
FROM users u
JOIN team_user tu ON tu.user_id = u.id;

-- Change 'guest' roles to 'member' (if any)
UPDATE team_user SET role = 'member' WHERE role = 'guest';

-- Assign admin role to a specific user (replace user_id and team_id)
UPDATE team_user SET role = 'admin' WHERE user_id = 1 AND team_id = 1;

-- Verify changes
SELECT u.id, u.name, u.email, tu.role
FROM users u
JOIN team_user tu ON tu.user_id = u.id;
```

#### Option B: Using Laravel Tinker

```bash
php artisan tinker
```

Then run:

```php
// Update a specific user to admin
$user = App\Models\User::where('email', 'admin@example.com')->first();
if ($user && $user->currentTeam) {
    $user->currentTeam->users()->updateExistingPivot($user->id, ['role' => 'admin']);
    echo "User updated to admin\n";
}

// Change all 'guest' users to 'member'
DB::table('team_user')->where('role', 'guest')->update(['role' => 'member']);
echo "All guest users updated to member\n";
```

### Step 4: Clear Caches

Clear Laravel caches to ensure the changes take effect:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Step 5: Verify the Update

1. Log out and log back in
2. Admin users should see the "Users" resource in the navigation menu
3. When creating posts as admin, you should be able to add categories
4. Admin should be able to view and edit all posts
5. Members should only see their own posts

## What's New in This Update

### 1. Role-Based Permissions

**Admin Role:**
- ✅ Manage users (create, view, edit, delete)
- ✅ View all posts
- ✅ Edit/delete any post
- ✅ Create/edit categories
- ✅ Create/edit tags

**Member Role:**
- ❌ Cannot manage users
- ✅ View only their own posts
- ✅ Edit/delete only their own posts
- ❌ Cannot create/edit categories (read-only)
- ✅ Create/edit tags

### 2. New Default Role

New users are now created with the `member` role by default (instead of `guest`).

### 3. Post Filtering

The posts list is automatically filtered based on user role:
- Admin sees all posts
- Members see only their own posts

### 4. Category Restrictions

Only admin users can create new categories when editing posts. Members can select from existing categories but cannot create new ones.

## Troubleshooting

### AuthServiceProvider Not Registered

If policies are not working, verify that `AuthServiceProvider` is registered in `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,  // ← This line should exist
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\JetstreamServiceProvider::class,
];
```

### Policies Not Found

If you get "Policy not found" errors, ensure the policy files exist:

- `app/Policies/PostPolicy.php`
- `app/Policies/UserPolicy.php`

If missing, run:

```bash
php artisan cms-core:update --force
```

### Users Can't Access Resources

If admin users can't access the Users resource:

1. Verify the user's role:
   ```sql
   SELECT u.email, tu.role FROM users u JOIN team_user tu ON tu.user_id = u.id;
   ```

2. Ensure the role is 'admin' (not 'guest' or 'member')

3. Clear caches:
   ```bash
   php artisan cache:clear
   ```

4. Log out and log back in

### Categories Field is Hidden for Admin

If admin users can't see the categories field:

1. Clear view cache:
   ```bash
   php artisan view:clear
   ```

2. Verify you're logged in as admin (check role in database)

3. Refresh the page

## Rolling Back (If Needed)

If you need to roll back the changes:

1. Remove the policies:
   ```bash
   rm app/Policies/PostPolicy.php
   rm app/Policies/UserPolicy.php
   ```

2. Remove AuthServiceProvider:
   ```bash
   rm app/Providers/AuthServiceProvider.php
   ```

3. Remove the registration from `bootstrap/providers.php`:
   ```php
   // Remove this line:
   App\Providers\AuthServiceProvider::class,
   ```

4. Clear caches:
   ```bash
   php artisan cache:clear
   ```

## Support

For issues or questions:

1. Check the [role-permissions-update.md](role-permissions-update.md) documentation
2. Review the changelog in the package repository
3. Open an issue on GitHub

## Version Requirements

- Laravel: 11.x or higher
- PHP: 8.2 or higher
- Filament: 4.x
- Jetstream: 5.x
