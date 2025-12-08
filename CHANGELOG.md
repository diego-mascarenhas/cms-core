# Changelog

All notable changes to `cms-core` will be documented in this file.

## v1.0.7 - 2024-12-08

### Fixed
- Configure Filament panel with ->default() and ->login() to properly register auth routes
- Fixes "Route [login] not defined" error
- Simplified authentication setup - Filament now handles all auth routing

### Changed
- AdminPanelProvider now includes ->default() and ->login() methods
- Removed complex bootstrap/app.php modifications
- Cleaner Filament authentication setup

## v1.0.6 - 2024-12-08

### Fixed
- Disable Fortify views to prevent Jetstream login from being accessible
- Forces all authentication to go through Filament panel at /admin/login
- Users are now always redirected to Filament's login page

## v1.0.5 - 2024-12-08

### Fixed
- Improved locale configuration to add APP_LOCALE=es to .env file
- Better regex pattern for updating app.php locale config

## v1.0.4 - 2024-12-08

### Fixed
- Removed invalid ->locale() method call in Filament panel
- Fixed locale configuration to use config/app.php properly
- Locale now properly inherited from Laravel app config

## v1.0.3 - 2024-12-08

### Added
- Auto-configure Spanish locale for application and Filament panel
- Configure Fortify to redirect to /admin after authentication
- Set Filament login as default authentication (instead of Jetstream)

### Fixed
- Authentication now uses Filament login at /admin/login
- Application locale properly set to Spanish (es)
- Root route redirects to /admin for immediate panel access

## v1.0.2 - 2024-12-08

### Fixed
- Always create personal team for admin user (Jetstream requirement)
- Fixed null pointer error when accessing currentTeam in navigation
- Clarified that APP_TEAMS only controls UI visibility, not team creation

### Changed
- Updated documentation to explain APP_TEAMS behavior:
  - Users always have a personal team (Jetstream technical requirement)
  - APP_TEAMS=false only hides team UI (switcher, create team, settings)
  - APP_TEAMS=true shows all team features for multi-tenant usage

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
