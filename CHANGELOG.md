# Changelog

All notable changes to `cms-core` will be documented in this file.

## v1.0.1 - 2024-12-08

### Fixed
- Removed bundled migrations to avoid conflicts with Jetstream and Spatie Permission
- Package now uses migrations from official dependencies (cleaner approach)

## v1.0.0 - 2024-12-08

### Added
- BelongsToCurrentTeam trait for multi-tenant scoping
- TeamSwitcher Livewire component
- CmsCorePlugin for Filament integration
- Teams toggle via APP_TEAMS environment variable
- Complete installation command with `--fresh` and `--seed` options
- Automatic admin user creation (hola@humano.app / Simplicity!)

### Features
- One-command setup: `php artisan cms-core:install --fresh --seed`
- Auto-installs and configures Jetstream, Filament, and Spatie Permission
- Auto-registers CmsCorePlugin in AdminPanelProvider
- Auto-updates User model with FilamentUser and HasRoles
- Auto-redirects root route to /admin
- Automatic team creation when teams are enabled
- FilamentUser interface documentation
- Comprehensive README with examples

### Requirements
- Laravel 12+
- Jetstream with Livewire stack (auto-installed)
- Filament 4 (auto-installed)
- Spatie Permission (auto-installed)

### Notes
- Migrations come from Jetstream and Spatie Permission packages (not bundled to avoid conflicts)
- Package uses official migrations from dependencies
