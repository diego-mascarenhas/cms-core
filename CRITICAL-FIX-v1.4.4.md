# 🚨 CRITICAL FIX v1.4.4 - Logout Error 405

## Problem Identified

The "Logout" button was generating **405 Method Not Allowed** error because:

- `CmsCorePlugin.php` customized logout with `MenuItem::make()->url()`
- This created an `<a href>` link using GET method
- Filament's `/admin/logout` route only accepts POST for security

## ✅ Solution Applied (v1.4.4)

- Removed custom logout MenuItem from `src/Filament/CmsCorePlugin.php`
- Filament now handles logout automatically with default behavior
- "Logout" button now uses POST correctly

## Additional Fix

- **DatabaseSeeder team_user relationship**: Admin user is now properly linked to their team with admin role
- Fixes issue where users couldn't be created due to empty `team_user` table

---

## 📤 STEP 1: Push to GitHub

```bash
cd /path/to/cms-core

# Push everything
git push origin main --tags
```

---

## 🔄 STEP 2: Update Projects

```bash
cd /path/to/your-project

# 1. Update package
composer update idoneo/cms-core

# 2. Clear caches
php artisan optimize:clear
```

---

## 🧪 STEP 3: Verify the Fix

### Test Logout

1. Login as any user
2. Click on your name (top right)  
3. Click "Logout"
4. ✅ Should work WITHOUT 405 error

### Test User Creation

1. Login as admin
2. Go to "Users" → "Create"
3. ✅ Should create users without errors

---

## 🎯 Versions

- **v1.4.0**: Permission system (initial)
- **v1.4.1**: Logout fix (AdminPanelProvider) + diagnostics + preselected role
- **v1.4.2**: FIX - AdminPanelProvider now publishes correctly
- **v1.4.3**: FIX - InstallCommand publishes correctly
- **v1.4.4**: CRITICAL FIX - Logout 405 error resolved + team_user relationship

---

🔥 **IMPORTANT**: v1.4.4 definitively resolves logout 405 error and team_user relationship issues.
