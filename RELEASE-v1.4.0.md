# Release v1.4.0 - Role-Based Permissions System

## ✅ Commit and Tag Created

The commit and tag have been created locally. Now you need to push them to GitHub.

## 📤 Push to GitHub

Run these commands to push the new version:

```bash
cd /Users/magoo/Sites/cms-core

# Push the commit
git push origin main

# Push the tag
git push origin v1.4.0
```

## 📦 What's Included in This Release

### New Files
- `app/Policies/PostPolicy.php` - Post access control
- `app/Policies/UserPolicy.php` - User management restrictions
- `app/Providers/AuthServiceProvider.php` - Policy registration
- `docs/role-permissions-update.md` - Technical documentation
- `docs/update-guide.md` - Complete update guide
- `UPDATE-QUICK.md` - Quick update reference

### Modified Files
- `src/Filament/Resources/UserResource.php` - Default role = 'member'
- `src/Filament/Resources/UserResource/Pages/CreateUser.php` - Role handling
- `src/Filament/Resources/PostResource.php` - Category restrictions
- `src/Filament/Resources/PostResource/Pages/ListPosts.php` - Post filtering
- `src/CmsCoreServiceProvider.php` - Added policy/provider publishing
- `src/Commands/UpdateCommand.php` - Auto-register AuthServiceProvider
- `bootstrap/providers.php` - AuthServiceProvider registered
- `CHANGELOG.md` - v1.4.0 release notes

## 🎯 What Users Need to Do

After pushing to GitHub, your users can update with:

```bash
# 1. Update package
composer update idoneo/cms-core

# 2. Run update command
php artisan cms-core:update --force

# 3. Update roles in database (SQL)
UPDATE team_user SET role = 'member' WHERE role = 'guest';
UPDATE team_user SET role = 'admin' WHERE user_id = 1 AND team_id = 1;

# 4. Clear caches
php artisan config:clear && php artisan cache:clear
```

## 📋 Features in v1.4.0

### Admin Role Can:
- ✅ Manage users (create, view, edit, delete)
- ✅ View all posts
- ✅ Edit/delete any post
- ✅ Create/edit categories
- ✅ Create/edit tags

### Member Role Can:
- ✅ View only their own posts
- ✅ Edit/delete only their own posts
- ✅ Create/edit tags
- ❌ Cannot manage users
- ❌ Cannot create/edit categories (read-only)

### System Changes:
- Default role changed from 'guest' to 'member'
- Automatic AuthServiceProvider registration
- Post filtering by user role
- Category management restricted to admin

## 🔄 Migration Path

### New Installations
- Everything works out of the box
- No manual configuration needed

### Existing Installations
- Run `composer update idoneo/cms-core`
- Run `php artisan cms-core:update --force`
- Manually update user roles in database
- Clear caches

## 📚 Documentation

- `UPDATE-QUICK.md` - Quick 3-step guide
- `docs/update-guide.md` - Detailed guide with troubleshooting
- `docs/role-permissions-update.md` - Technical documentation
- `CHANGELOG.md` - Full changelog

## ✨ Breaking Changes

- Existing users with 'guest' role should be changed to 'member'
- AuthServiceProvider must be registered (auto-done by update command)
- Manual database update required for existing installations

## 🎉 Ready to Release!

Once you push to GitHub, the new version will be available via Composer.

Users will see it when they run:
```bash
composer show idoneo/cms-core
```

And can update with:
```bash
composer update idoneo/cms-core
```
