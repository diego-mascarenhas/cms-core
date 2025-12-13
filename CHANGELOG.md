# Changelog

All notable changes to `cms-core` will be documented in this file.

## v1.3.6 - 2024-12-13

### Fixed
- **Fixed duplicate teams migrations** - Generic method `removeDuplicateMigrations()` now handles duplicate migrations for:
  - `create_teams_table` (prevents "Table already exists" errors)
  - `create_team_user_table` (prevents "Table already exists" errors)
  - `create_team_invitations_table` (prevents "Table already exists" errors)
- Replaced specific `removeDuplicateTwoFactorMigrations()` with generic `removeDuplicateMigrations()` method
- Method now handles multiple migration types in a single pass for better performance

## v1.3.5 - 2024-12-13

### Fixed
- **Fixed Media Library migrations tag** - Changed from `migrations` to `medialibrary-migrations` to correctly publish media table
- **Improved tags migration handling** - Automatically removes duplicate tags migrations if table already exists
- `renameTagsMigration()` now checks if `tags` table exists and removes duplicate migrations instead of creating new ones
- **Fixed duplicate migrations** - New generic method `removeDuplicateMigrations()` automatically removes duplicate migrations for:
  - `two_factor_columns_to_users_table` (prevents "Duplicate column name" errors)
  - `create_teams_table` (prevents "Table already exists" errors)
  - `create_team_user_table` (prevents "Table already exists" errors)
  - `create_team_invitations_table` (prevents "Table already exists" errors)
- Simplified migration duplicate detection (no longer queries migrations table, just checks if table exists)
- Use `--force` flag with `cms-core:update` to force overwrite existing files (e.g., `php artisan cms-core:update --force`)

## v1.3.4 - 2024-12-13

### Fixed
- **Fixed Media Library migrations tag** - Changed from `migrations` to `medialibrary-migrations` to correctly publish media table
- **Improved tags migration handling** - Automatically removes duplicate tags migrations if table already exists
- `renameTagsMigration()` now checks if `tags` table exists and removes duplicate migrations instead of creating new ones
- **Fixed duplicate two_factor migrations** - Automatically removes duplicate `two_factor_columns_to_users_table` migrations
- New method `removeDuplicateTwoFactorMigrations()` prevents "Duplicate column name" errors
- Simplified migration duplicate detection (no longer queries migrations table, just checks if table exists)
- Use `--force` flag with `cms-core:update` to force overwrite existing files (e.g., `php artisan cms-core:update --force`)

## v1.3.2 - 2024-12-13

### Fixed
- **Post model now automatically published during installation** - Fixed "Class App\Models\Post not found" error
- Post model is now included in `cms-core-models` publishable tag
- Models are automatically published during `cms-core:install` and `cms-core:update`
- **Spatie Media Library migrations now automatically published** - Fixed missing `media` table
- Media Library migrations are now included in installation and update commands

## v1.3.0 - 2024-12-13

### Added
- **REST API for Posts** with authentication via Sanctum tokens
  - `GET /api/posts` - List posts with filters (status, category, tag, search, pagination)
  - `GET /api/posts/{slug}` - Get post details by slug
  - Full filtering support: status, category, tag, search
  - Pagination support (per_page, page)
  - Team scoping automatically applied when teams are enabled
- **API Token Management**
  - New command `cms-core:api-token` to generate API tokens
  - Support for `APP_TOKEN` environment variable for application-level authentication
  - `AuthenticateApiToken` middleware for token-based authentication
- **API Resources**
  - `PostResource` for formatted API responses
  - Includes featured images, gallery, categories, and tags
  - Author information included in responses
- **API Documentation**
  - Complete API documentation in README
  - cURL examples for all endpoints
  - Authentication guide with token generation
- **Automatic API Setup**
  - API files (controllers, requests, resources, middleware) are automatically published
  - API routes are automatically registered during installation/update
  - `cms-core:update` command now includes API files and routes

### Changed
- `cms-core:update` command now publishes API files and registers routes automatically
- README updated with comprehensive API documentation and examples

### Fixed
- Improved route registration to prevent duplicates
- Better error handling in API endpoints

## v1.2.10 - 2024-12-09

### Fixed
- Improved updateJetstreamRoles to handle both 'viewer' and 'editor' roles
- Now replaces 'viewer' → 'guest' automatically
- Better detection and messaging for role updates

## v1.2.9 - 2024-12-09

### Added
- Install command now updates JetstreamServiceProvider roles automatically
- Replaces old 'editor' role with 'member' and 'guest' roles during installation

### Changed
- Standard roles are now: admin (Administrator), member (Member), guest (Guest)
- Removed deprecated 'editor' role in favor of 'member' role

## v1.2.8 - 2024-12-09

### Fixed
- Removed custom validationMessages to allow Laravel automatic validation translations
- Laravel now uses lang/es/validation.php automatically for all validation messages
- Validation messages now display in correct Spanish format (e.g., "El campo nombre es obligatorio.")

## v1.2.7 - 2024-12-09

### Fixed
- EditUser now correctly updates user role using both syncWithoutDetaching and updateExistingPivot
- Role changes now properly persist when editing existing users

## v1.2.6 - 2024-12-09

### Added
- DatabaseSeeder now publishes to application via 'cms-core-seeders' tag
- Install command now updates Team model to add 'user_id' to fillable array
- EditUser now creates personal team if missing when saving user
- Comprehensive validation rules for all User form fields (name, email, password, phone, role)
- Complete Spanish validation translations file (resources/lang/es/validation.php)
- Custom validation messages for each field with proper Spanish translations
- Phone field validation: minimum 10 characters, maximum 20 characters
- Password field validation: minimum 8 characters
- Name field validation: minimum 3 characters
- Disabled HTML5 native validation using extraInputAttributes(['required' => false]) to prevent browser tooltips and show custom error messages below fields

### Fixed
- Translation files now publish to correct path (lang/es.json instead of lang/vendor/cms-core/es.json)
- Team model mass assignment error when creating users with personal teams
- User role not saving when currentTeam was null in EditUser page
- EditUser now uses syncWithoutDetaching instead of updateExistingPivot for better reliability
- Table column labels now use translation keys instead of hardcoded Spanish text

## v1.2.5 - 2024-12-08

### Fixed
- KeyValue component for 'data' field now handles NULL values correctly with default([]) and proper dehydration

## v1.2.4 - 2024-12-08

### Added
- Migrations publishing via 'cms-core-migrations' tag
- Install command now publishes CMS Core migrations automatically

### Changed
- APP_TEAMS now defaults to false (single-tenant mode) instead of requiring explicit configuration
- Updated installation instructions to reflect new default behavior

### Fixed
- Install command now properly configures custom Dashboard page in AdminPanelProvider
- Install command removes default Filament widgets (AccountWidget, FilamentInfoWidget)
- Install command now adds 'phone' and 'data' to User model $fillable
- Install command now adds 'data' => 'array' cast to User model
- Phone field uses bigInteger type for numeric phone storage

## v1.2.3 - 2024-12-08

### Added
- Spanish translation file (resources/lang/es.json) with all UI labels
- Translation support via __() for all user-facing text
- registerTranslations() method in CmsCoreServiceProvider to load translations

### Changed
- All labels, comments and variables now in English with translation support via __()
- Default role changed from 'viewer' to 'guest' across all files
- Custom Dashboard page without header widgets and empty heading
- Profile menu item now hidden when APP_TEAMS is disabled
- Navigation label for dashboard uses __('Dashboard')

### Fixed
- Widget registration moved from plugin to Dashboard page to avoid Livewire component errors

## v1.2.2 - 2024-12-08

### Fixed
- Added `laravel/sanctum` to package dependencies to ensure `HasApiTokens` exists during installs

## v1.2.1 - 2024-12-08

### Added
- Dashboard widgets with user statistics
- UserStatsOverview widget showing total users, verified users, users by role, and new users this month
- UsersChart widget displaying user growth over last 6 months
- Automatic registration of widgets in CmsCorePlugin

### Changed
- Removed default Filament widgets (AccountWidget, FilamentInfoWidget) from AdminPanelProvider
- Dashboard now shows user-focused statistics instead of generic information

## v1.2.0 - 2024-12-08

### Added
- Phone field (INT, nullable) to users table via migration
- Data field (JSON, nullable) to users table for additional key-value storage
- Dynamic Jetstream role selection in user forms (reads from JetstreamServiceProvider)
- Role display column in users table with color-coded badges
- Three default roles: Administrator (admin), Member (member), Viewer (viewer)

### Changed
- UserResource fully compatible with Filament 4 (removed deprecated BulkActionGroup)
- Form fields now use flat structure instead of Section components
- Password field repositioned next to Name field in user form
- Role field spans full width with helper text
- Default user role changed from 'editor' to 'viewer' (most restrictive)
- Team model includes user_id in fillable array
- DatabaseSeeder creates admin user with hola@humano.app / Simplicity!

### Fixed
- Compatibility with Filament 4 table and form components
- Team creation now properly assigns user_id
- Role assignment correctly stored in team_user pivot table
- User model includes phone and data in fillable with proper casts

### Technical
- Role colors: admin (green), member (blue), viewer (yellow)
- KeyValue component for data field (JSON key-value pairs)
- Roles loaded dynamically from Jetstream::$roles
- CreateUser and EditUser pages handle role assignment automatically

## v1.1.0 - 2024-12-08

### Added
- User management resource in Filament admin panel
- Complete CRUD for users with roles and permissions
- Filter users by role
- Suggested team roles: Admin, Member, Viewer (more generic than Editor)

### Features
- UserResource automatically registered in admin panel
- Users menu item with icon
- Create, edit, delete users from admin panel
- Assign roles and permissions directly in the UI
- Search and sort users
- Email verification status display

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
