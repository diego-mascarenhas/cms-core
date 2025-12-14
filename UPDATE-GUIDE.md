# Update Guide to v1.4.4

## ✅ Version v1.4.4 Released (CRITICAL FIX - Logout Error 405)

- **Commit**: Fix logout error 405 and team_user relationship
- **Tag**: v1.4.4
- **Fixes**: 
  - Removed custom logout causing 405 error
  - DatabaseSeeder now creates team_user relationship

---

## 📤 1. Push to Repository (If you're maintaining the package)

```bash
cd /path/to/cms-core

# Push commits
git push origin main

# Push tags
git push origin --tags
```

---

## 🔄 2. Update Your Project

```bash
cd /path/to/your-project

# A. Update the package
composer update idoneo/cms-core

# B. Clear caches
php artisan optimize:clear
```

---

## ✨ 3. What Gets Fixed

### Fix #1: Logout Error 405
- ✅ "Logout" button will work without 405 error
- ✅ Now uses POST method correctly (handled by Filament)
- ✅ No more "Method Not Allowed"

### Fix #2: Team User Relationship
- ✅ Admin user properly linked to team with admin role
- ✅ Users can now be created without errors
- ✅ DatabaseSeeder ensures team_user pivot table relationship

---

## 🧪 4. Verification (After Update)

### Test Logout:
1. Login as any user
2. Click on your name (top right)
3. Click "Logout"
4. ✅ Should work without error

### Test User Creation:
1. Login as admin
2. Go to "Users" → "Create User"
3. Fill form and save
4. ✅ User should be created successfully

---

## 🚨 If Something Doesn't Work

### Policies not working (member still sees users):

```bash
# 1. Run diagnostic
php artisan cms-core:diagnose-policies

# 2. Check if AuthServiceProvider is registered
cat bootstrap/providers.php | grep AuthServiceProvider

# 3. If not there, add it manually to bootstrap/providers.php:
# App\Providers\AuthServiceProvider::class,

# 4. Clear everything
php artisan optimize:clear

# 5. Logout and login again
```

### Team_user table empty:

If you installed v1.4.3 or earlier and users can't be created:

```bash
# Run this to fix existing admin user
php artisan tinker
```

```php
$admin = User::first();
if ($admin && $admin->currentTeam) {
    $admin->currentTeam->users()->syncWithoutDetaching([
        $admin->id => ['role' => 'admin']
    ]);
    echo "Fixed admin user team relationship\n";
}
exit
```

---

## 🐛 5. Troubleshooting

### If logout still fails:

```bash
# 1. Check package version
composer show idoneo/cms-core | grep version

# 2. Clear EVERYTHING
php artisan optimize:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# 3. Restart server (if using Herd)
# Cmd+Q on Herd and reopen
```

---

## 📋 Summary of Changes in v1.4.4

### Fixed:
- ✅ 405 error on logout (definitively resolved)
- ✅ Removed custom logout MenuItem from CmsCorePlugin
- ✅ Filament handles logout automatically with POST
- ✅ DatabaseSeeder now creates team_user relationship
- ✅ Admin user properly linked with admin role

---

## ✅ Final Checklist

- [ ] Pushed v1.4.4
- [ ] Updated project with `composer update`
- [ ] Ran `php artisan optimize:clear`
- [ ] Tested logout - works without 405 error ✓
- [ ] Tested user creation - works correctly ✓
- [ ] Member doesn't see "Users" menu ✓

---

🎉 **Done! Project updated to v1.4.4**
