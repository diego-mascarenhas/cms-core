# Quick Update Guide for Existing Installations

## TL;DR - Quick Steps

For existing CMS-Core installations, follow these 3 simple steps:

### 1. Update the Package

```bash
composer update idoneo/cms-core
```

### 2. Run Update Command

```bash
php artisan cms-core:update --force
```

This publishes:
- ✅ PostPolicy.php
- ✅ UserPolicy.php  
- ✅ AuthServiceProvider.php
- ✅ Registers AuthServiceProvider automatically

### 3. Update User Roles (SQL)

```sql
-- Change guest to member
UPDATE team_user SET role = 'member' WHERE role = 'guest';

-- Set first user as admin
UPDATE team_user SET role = 'admin' WHERE user_id = 1 AND team_id = 1;
```

### 4. Clear Caches

```bash
php artisan config:clear && php artisan cache:clear
```

### 5. Verify

- Log out and back in
- Admin should see "Users" menu
- Admin can manage categories in posts
- Members only see their own posts

---

## What Changed?

### New Default Role
- New users: `member` (was `guest`)

### Admin Can
- ✅ Manage users
- ✅ View all posts
- ✅ Edit any post
- ✅ Create categories

### Member Can
- ✅ View own posts only
- ✅ Edit own posts only
- ✅ Create tags
- ❌ No user management
- ❌ No category creation

---

## Troubleshooting

### Policies Not Working?

Check `bootstrap/providers.php` includes:
```php
App\Providers\AuthServiceProvider::class,
```

### Still Can't Access Users?

1. Check role in database:
   ```sql
   SELECT u.email, tu.role FROM users u 
   JOIN team_user tu ON tu.user_id = u.id;
   ```

2. Role should be `'admin'` not `'guest'`

3. Log out and back in

---

For detailed instructions, see: `docs/update-guide.md`
