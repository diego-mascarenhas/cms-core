# Release Notes - v1.4.4

## Critical Fixes

### Logout Error 405 (RESOLVED) ✅

**Problem**: The logout button was throwing a "405 Method Not Allowed" error when users tried to log out.

**Root Cause**: 
- `CmsCorePlugin.php` was customizing the logout menu item with `MenuItem::make()->url()`
- This created a regular `<a href>` link using GET method
- Filament's `/admin/logout` route only accepts POST for security

**Solution**:
- Removed custom logout MenuItem from `src/Filament/CmsCorePlugin.php` (lines 99-104)
- Filament now handles logout automatically with its default behavior
- The logout button now correctly uses POST method via JavaScript

**Files Changed**:
- `src/Filament/CmsCorePlugin.php`

---

### Team User Relationship (RESOLVED) ✅

**Problem**: Users couldn't be created, and the "Users" menu wasn't appearing for admin users.

**Root Cause**: 
- `DatabaseSeeder` was creating users with `withPersonalTeam()` but not creating the pivot relationship in `team_user` table
- The table remained empty, causing role checks to fail
- Policies depended on this relationship to determine user roles

**Solution**:
- Updated `DatabaseSeeder` to explicitly create the `team_user` relationship
- Admin user is now properly linked to their team with 'admin' role
- Added `syncWithoutDetaching` to ensure pivot table is populated

**Files Changed**:
- `database/seeders/DatabaseSeeder.php`

**Code Added**:
```php
// Ensure team_user relationship exists with admin role
if ($admin->currentTeam) {
    $admin->currentTeam->users()->syncWithoutDetaching([
        $admin->id => ['role' => 'admin']
    ]);
}
```

---

## Documentation Updates

### New Documentation
- **UPDATE-GUIDE.md**: Comprehensive update guide for v1.4.4
- **CRITICAL-FIX-v1.4.4.md**: Detailed explanation of critical fixes
- **POLICIES-GUIDE.md**: Complete guide for setting up and troubleshooting policies

### Removed Documentation (Spanish)
- Removed Spanish documentation files in favor of English versions
- Old files: `FIX-CRITICO-v1.4.2.md`, `PASOS-ACTUALIZACION.md`, `FIX-POLICIES-NOW.md`, `UPDATE-QUICK.md`

---

## Upgrade Instructions

### For New Installations

Simply run:
```bash
composer require idoneo/cms-core
php artisan cms-core:install --fresh --seed
```

The seeder will now correctly create the team_user relationship.

### For Existing Installations

1. Update the package:
   ```bash
   composer update idoneo/cms-core
   ```

2. If you have an empty `team_user` table, fix it manually:
   ```bash
   php artisan tinker
   ```
   
   ```php
   $admin = User::first();
   if ($admin && $admin->currentTeam) {
       $admin->currentTeam->users()->syncWithoutDetaching([
           $admin->id => ['role' => 'admin']
       ]);
   }
   exit
   ```

3. Clear caches:
   ```bash
   php artisan optimize:clear
   ```

4. Test logout and user creation functionality

---

## Testing Checklist

After updating to v1.4.4, verify:

- [ ] ✅ Logout button works without 405 error
- [ ] ✅ Admin user can see "Users" menu
- [ ] ✅ Admin user can create new users
- [ ] ✅ Member user doesn't see "Users" menu
- [ ] ✅ Role statistics show correct count in dashboard
- [ ] ✅ Policies are working (run `php artisan cms-core:diagnose-policies`)

---

## Version History

- **v1.4.0**: Initial permission system
- **v1.4.1**: Logout fix attempt + diagnostics + preselected role
- **v1.4.2**: AdminPanelProvider publishing fix
- **v1.4.3**: InstallCommand publishing fix  
- **v1.4.4**: **Definitive logout fix + team_user relationship**

---

## Breaking Changes

None. This is a patch release with bug fixes only.

---

## Contributors

Special thanks to everyone who reported these issues and helped test the fixes.

---

## Support

If you encounter any issues:

1. Run diagnostics: `php artisan cms-core:diagnose-policies`
2. Check the [UPDATE-GUIDE.md](UPDATE-GUIDE.md) for troubleshooting steps
3. Review [POLICIES-GUIDE.md](POLICIES-GUIDE.md) for policy setup
4. Clear all caches: `php artisan optimize:clear`

---

**Release Date**: December 14, 2024  
**Stability**: Stable  
**Recommended**: Yes - Critical bug fixes
