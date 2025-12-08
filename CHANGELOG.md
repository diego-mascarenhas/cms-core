# Changelog

All notable changes to `cms-core` will be documented in this file.

## v1.0.0 - 2024-12-08

### Added
- BelongsToCurrentTeam trait for multi-tenant scoping
- TeamSwitcher Livewire component
- CmsCorePlugin for Filament integration
- Teams toggle via APP_TEAMS environment variable
- Complete installation command with `--fresh` and `--seed` options
- Bundled migrations (Laravel base, Spatie Permission only - Jetstream migrations excluded to avoid conflicts)
- Automatic admin user creation (hola@humano.app / Simplicity!)

### Features
- One-command setup: `php artisan cms-core:install --fresh --seed`
- Automatic team creation when teams are enabled
- FilamentUser interface documentation
- Comprehensive README with examples

### Requirements
- Laravel 12+
- Jetstream with Livewire stack (must be installed first)
- Filament 4
- Spatie Permission
