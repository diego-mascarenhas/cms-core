<?php

namespace Idoneo\CmsCore\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class InstallCommand extends Command
{
	protected $signature = 'cms-core:install
		{--fresh : Run fresh migrations}
		{--seed : Create admin user}
		{--skip-jetstream : Skip Jetstream installation}';

	protected $description = 'Install CMS-Core package with Jetstream, migrations and admin user';

	public function handle(): int
	{
		$this->info('Installing CMS-Core...');
		$this->newLine();

		// Install Jetstream if not skipped
		if (!$this->option('skip-jetstream'))
		{
			$this->info('Installing Jetstream with Livewire + Teams...');
			$this->call('jetstream:install', [
				'stack' => 'livewire',
				'--teams' => true,
				'--dark' => false,
			]);
			$this->info('✓ Jetstream installed');
			$this->newLine();
		}

		// Install Filament panel
		$this->info('Installing Filament panel...');
		$this->call('filament:install', [
			'--panels' => true,
		]);
		$this->info('✓ Filament panel installed');

		// Register CmsCore plugin in AdminPanelProvider
		$this->registerPlugin();

	// Update User model
	$this->updateUserModel();

	// Update Team model
	$this->updateTeamModel();

	// Update Jetstream roles
	$this->updateJetstreamRoles();

	// Update web routes
	$this->updateWebRoutes();

	// Register API routes
	$this->registerApiRoutes();

		// Update Fortify config to use Filament login
		$this->updateFortifyConfig();

		// Update app locale
		$this->updateLocale();

		// Update bootstrap app.php
		$this->updateBootstrapApp();
		$this->newLine();

		// Publish config
		$this->call('vendor:publish', [
			'--tag' => 'cms-core-config',
			'--force' => true,
		]);
		$this->info('✓ Config published');

		// Publish Spatie Permission migrations
		$this->call('vendor:publish', [
			'--provider' => 'Spatie\Permission\PermissionServiceProvider',
		]);
		$this->info('✓ Permission migrations published');

		// Publish Spatie Tags migrations
		$this->call('vendor:publish', [
			'--provider' => 'Spatie\Tags\TagsServiceProvider',
			'--tag' => 'tags-migrations',
		]);
		$this->info('✓ Tags migrations published');

		// Publish Spatie Media Library migrations
		$this->call('vendor:publish', [
			'--provider' => 'Spatie\MediaLibrary\MediaLibraryServiceProvider',
			'--tag' => 'medialibrary-migrations',
		]);
		$this->info('✓ Media Library migrations published');

		// Rename tags migration to sequential format
		$this->renameTagsMigration();

		// Remove duplicate migrations (two_factor, teams, etc.)
		$this->removeDuplicateMigrations();

		// Publish CMS Core migrations
		$this->call('vendor:publish', [
			'--tag' => 'cms-core-migrations',
			'--force' => true,
		]);
		$this->info('✓ CMS Core migrations published');

		// Publish views
		$this->call('vendor:publish', [
			'--tag' => 'cms-core-views',
			'--force' => true,
		]);
		$this->info('✓ Views published');

	// Publish translations
	$this->call('vendor:publish', [
		'--tag' => 'cms-core-lang',
		'--force' => true,
	]);
	$this->info('✓ Translations published');

	// Publish seeders
	$this->call('vendor:publish', [
		'--tag' => 'cms-core-seeders',
		'--force' => true,
	]);
	$this->info('✓ Seeders published');

	// Publish models
	$this->call('vendor:publish', [
		'--tag' => 'cms-core-models',
		'--force' => true,
	]);
	$this->info('✓ Models published');

	// Publish API files (controllers, requests, resources, middleware)
	$this->call('vendor:publish', [
		'--tag' => 'cms-core-api',
		'--force' => true,
	]);
	$this->info('✓ API files published');

	// Run migrations
		if ($this->option('fresh'))
		{
			$this->newLine();
			$this->warn('Running fresh migrations...');
			$this->call('migrate:fresh');
		}
		else
		{
			$this->newLine();
			$this->info('Running migrations...');
			$this->call('migrate');
		}

		// Create admin user
		if ($this->option('seed'))
		{
			$this->newLine();
			$this->info('Creating admin user...');
			$this->createAdminUser();
		}

		$this->newLine();
		$this->info('CMS-Core installed successfully!');
		$this->newLine();

		$this->comment('Next steps:');
		$this->line('  1. Run: npm install && npm run build');
		$this->line('  2. Add APP_TEAMS=true to .env if you need multi-tenant mode (default: false)');
		$this->line('  3. Access admin panel at: /admin');

		if ($this->option('seed'))
		{
			$this->newLine();
			$this->comment('Admin credentials:');
			$this->line('  - Email: hola@humano.app');
			$this->line('  - Password: Simplicity!');
		}

		return self::SUCCESS;
	}

	protected function createAdminUser(): void
	{
		$userModel = config('cms-core.user_model', \App\Models\User::class);
		$teamModel = config('cms-core.team_model', \App\Models\Team::class);

		// Check if user already exists
		if ($userModel::where('email', 'hola@humano.app')->exists())
		{
			$this->warn('  Admin user already exists, skipping...');
			return;
		}

		// Create user with personal team
		$user = $userModel::create([
			'name' => 'Admin',
			'email' => 'hola@humano.app',
			'password' => Hash::make('Simplicity!'),
			'email_verified_at' => now(),
		]);

		// Create personal team (Jetstream requires it)
		if (method_exists($user, 'ownedTeams'))
		{
			$team = $teamModel::forceCreate([
				'user_id' => $user->id,
				'name' => explode(' ', $user->name, 2)[0]."'s Team",
				'personal_team' => true,
			]);

			$user->current_team_id = $team->id;
			$user->save();
		}

		$this->info('  ✓ Admin user created with personal team');
	}

	protected function registerPlugin(): void
	{
		$providerPath = app_path('Providers/Filament/AdminPanelProvider.php');

		if (!file_exists($providerPath))
		{
			$this->warn('  AdminPanelProvider not found, skipping plugin registration');
			return;
		}

		$content = file_get_contents($providerPath);

		// Check if already registered
		if (str_contains($content, 'CmsCorePlugin'))
		{
			$this->info('✓ CmsCore plugin already registered');
			return;
		}

		// Add use statements for plugin and custom dashboard
		if (!str_contains($content, 'use Idoneo\CmsCore\Filament\CmsCorePlugin;'))
		{
			$content = str_replace(
				'use Filament\Widgets\FilamentInfoWidget;',
				"use Filament\Widgets\FilamentInfoWidget;\nuse Idoneo\CmsCore\Filament\CmsCorePlugin;",
				$content
			);
		}

		// Replace default Dashboard with custom one
		$content = str_replace(
			'use Filament\Pages\Dashboard;',
			'use Idoneo\CmsCore\Filament\Pages\Dashboard;',
			$content
		);

		// Replace Dashboard::class in pages array
		$content = str_replace(
			'->pages([' . "\n" . '                Dashboard::class,',
			'->pages([' . "\n" . '                \Idoneo\CmsCore\Filament\Pages\Dashboard::class,',
			$content
		);

		// Remove default widgets (AccountWidget and FilamentInfoWidget)
		$content = preg_replace(
			'/->widgets\(\[\s*AccountWidget::class,\s*FilamentInfoWidget::class,\s*\]\)/s',
			'->widgets([' . "\n" . '                // Dashboard widgets are registered by CmsCorePlugin' . "\n" . '            ])',
			$content
		);

		// Add default(), login() and plugin
		$content = preg_replace(
			'/(\->id\(\'admin\'\))(\s*\->)/s',
			'$1' . "\n            ->default()\n            ->login()" . '$2',
			$content,
			1
		);

		$content = preg_replace(
			'/(\->colors\(\[.*?\]\))(\s*\->)/s',
			'$1' . "\n            ->plugin(CmsCorePlugin::make())" . '$2',
			$content,
			1
		);

		file_put_contents($providerPath, $content);
		$this->info('✓ CmsCore plugin registered in AdminPanelProvider');
	}

	protected function updateUserModel(): void
	{
		$userModelPath = app_path('Models/User.php');

		if (!file_exists($userModelPath))
		{
			$this->warn('  User model not found, skipping update');
			return;
		}

		$content = file_get_contents($userModelPath);

		// Check if already updated
		if (str_contains($content, 'FilamentUser'))
		{
			$this->info('✓ User model already updated');
			return;
		}

		// Add use statements for Spatie Permission and FilamentUser
		if (!str_contains($content, 'use Spatie\Permission\Traits\HasRoles;'))
		{
			$content = str_replace(
				'use Laravel\Sanctum\HasApiTokens;',
				"use Laravel\Sanctum\HasApiTokens;\nuse Spatie\Permission\Traits\HasRoles;\nuse Filament\Models\Contracts\FilamentUser;\nuse Filament\Panel;",
				$content
			);
		}

		// Add FilamentUser interface
		$content = preg_replace(
			'/class User extends Authenticatable/',
			'class User extends Authenticatable implements FilamentUser',
			$content
		);

		// Add HasRoles trait
		$content = preg_replace(
			'/(use HasProfilePhoto;)/',
			"$1\n    use HasRoles;",
			$content
		);

		// Add canAccessPanel method before closing brace
		if (!str_contains($content, 'canAccessPanel'))
		{
			$content = preg_replace(
				'/(\n}\s*)$/',
				"\n\n    public function canAccessPanel(Panel \$panel): bool\n    {\n        return true;\n    }\n}",
				$content
			);
		}

		// Add phone and data to fillable array if not already present
		if (!str_contains($content, "'phone'") && str_contains($content, 'protected $fillable'))
		{
			$content = preg_replace(
				"/(protected \\\$fillable\s*=\s*\[)/",
				"$1\n        'phone',\n        'data',",
				$content
			);
		}

		// Add data to casts array if not already present
		if (!str_contains($content, "'data'") && str_contains($content, 'protected $casts'))
		{
			$content = preg_replace(
				"/(protected function casts\(\): array\s*\{\s*return\s*\[)/",
				"$1\n            'data' => 'array',",
				$content
			);
		}

		file_put_contents($userModelPath, $content);
		$this->info('✓ User model updated with FilamentUser, HasRoles, phone and data fields');
	}

	protected function updateTeamModel(): void
	{
		$teamModelPath = app_path('Models/Team.php');

		if (!file_exists($teamModelPath))
		{
			$this->warn('  Team model not found, skipping update');
			return;
		}

		$content = file_get_contents($teamModelPath);

		// Check if user_id is already in fillable
		if (str_contains($content, "'user_id'"))
		{
			$this->info('✓ Team model already updated');
			return;
		}

		// Add user_id to fillable array after 'name'
		$content = preg_replace(
			"/(protected \\\$fillable\s*=\s*\[\s*'name',)/",
			"$1\n        'user_id',",
			$content
		);

		file_put_contents($teamModelPath, $content);
		$this->info('✓ Team model updated with user_id in fillable');
	}

	protected function updateJetstreamRoles(): void
	{
		$providerPath = app_path('Providers/JetstreamServiceProvider.php');

		if (!file_exists($providerPath))
		{
			$this->warn('  JetstreamServiceProvider not found, skipping update');
			return;
		}

		$content = file_get_contents($providerPath);

	// Check if already has the correct roles
	if (str_contains($content, "Jetstream::role('member', 'Member'") &&
		str_contains($content, "Jetstream::role('guest', 'Guest'"))
	{
		$this->info('✓ Jetstream roles already updated');
		return;
	}

	// Replace viewer role with guest role
	if (str_contains($content, "Jetstream::role('viewer'"))
	{
		$content = preg_replace(
			"/Jetstream::role\('viewer', 'Viewer'/",
			"Jetstream::role('guest', 'Guest'",
			$content
		);
		$content = str_replace(
			"Viewer users can only read content.",
			"Guest users can only read content.",
			$content
		);
		file_put_contents($providerPath, $content);
		$this->info('✓ Jetstream roles updated (viewer → guest)');
		return;
	}

	// Try to replace old editor role pattern
	$oldEditorPattern = "/Jetstream::role\('editor'.*?\)->description\('.*?'\);/s";

	if (preg_match($oldEditorPattern, $content))
	{
		$newRoles = "Jetstream::role('member', 'Member', [
            'read',
            'create',
            'update',
        ])->description('Member users have the ability to read, create, and update.');

        Jetstream::role('guest', 'Guest', [
            'read',
        ])->description('Guest users can only read content.');";

		$content = preg_replace($oldEditorPattern, $newRoles, $content);
		file_put_contents($providerPath, $content);
		$this->info('✓ Jetstream roles updated (editor → member + guest)');
	}
	else
	{
		$this->warn('  Could not find editor/viewer role pattern, roles look good or need manual update');
	}
	}

	protected function updateWebRoutes(): void
	{
		$routesPath = base_path('routes/web.php');

		if (!file_exists($routesPath))
		{
			$this->warn('  web.php not found, skipping route update');
			return;
		}

		$content = file_get_contents($routesPath);

		// Check if already redirects to admin
		if (str_contains($content, "redirect('/admin')"))
		{
			$this->info('✓ Root route already redirects to /admin');
			return;
		}

		// Replace default route
		$content = preg_replace(
			"/Route::get\('\/'\s*,\s*function\s*\(\)\s*\{\s*return\s+view\('welcome'\);?\s*\}\);?/",
			"Route::get('/', function () {\n    return redirect('/admin');\n});",
			$content
		);

		file_put_contents($routesPath, $content);
		$this->info('✓ Root route redirects to /admin');
	}

	protected function registerApiRoutes(): void
	{
		$apiRoutesPath = base_path('routes/api.php');

		if (!file_exists($apiRoutesPath))
		{
			$this->warn('  api.php not found, skipping API routes registration');
			return;
		}

		$content = file_get_contents($apiRoutesPath);

		// Check if CMS-Core API routes are already registered
		if (str_contains($content, 'cms-core::posts') || str_contains($content, 'PostController'))
		{
			$this->info('✓ API routes already registered');
			return;
		}

		// Add CMS-Core API routes
		$apiRoutes = "\n// CMS-Core API Routes\n";
		$apiRoutes .= "use App\\Http\\Controllers\\Api\\PostController;\n";
		$apiRoutes .= "use App\\Http\\Middleware\\AuthenticateApiToken;\n\n";
		$apiRoutes .= "// Posts API - Requires authentication via Bearer token or APP_TOKEN from .env\n";
		$apiRoutes .= "Route::middleware(['auth:sanctum', AuthenticateApiToken::class])->group(function () {\n";
		$apiRoutes .= "    Route::get('/posts', [PostController::class, 'index']);\n";
		$apiRoutes .= "    Route::get('/posts/{slug}', [PostController::class, 'show']);\n";
		$apiRoutes .= "});\n";

		// Append to the end of the file
		$content .= $apiRoutes;

		file_put_contents($apiRoutesPath, $content);
		$this->info('✓ API routes registered');
	}

	protected function updateFortifyConfig(): void
	{
		$fortifyPath = config_path('fortify.php');

		if (!file_exists($fortifyPath))
		{
			$this->warn('  fortify.php not found, skipping');
			return;
		}

		$content = file_get_contents($fortifyPath);

		// Update home redirect to /admin
		$content = preg_replace(
			"/'home' => '.*?'/",
			"'home' => '/admin'",
			$content
		);

		// Disable Fortify views to use only Filament login
		$content = preg_replace(
			"/'views' => true/",
			"'views' => false",
			$content
		);

		file_put_contents($fortifyPath, $content);
		$this->info('✓ Fortify configured to use Filament login');
	}

	protected function updateLocale(): void
	{
		$appConfigPath = config_path('app.php');

		if (!file_exists($appConfigPath))
		{
			$this->warn('  app.php not found, skipping');
			return;
		}

		$content = file_get_contents($appConfigPath);

		// Update locale default to Spanish
		$content = preg_replace(
			"/'locale' => env\('APP_LOCALE', '.*?'\)/",
			"'locale' => env('APP_LOCALE', 'es')",
			$content
		);

		file_put_contents($appConfigPath, $content);
		$this->info('✓ App locale default set to Spanish');

		// Update .env file
		$envPath = base_path('.env');
		if (file_exists($envPath))
		{
			$envContent = file_get_contents($envPath);
			if (!str_contains($envContent, 'APP_LOCALE='))
			{
				file_put_contents($envPath, $envContent . "\nAPP_LOCALE=es\n");
				$this->info('✓ APP_LOCALE=es added to .env');
			}
		}
	}

	protected function updateBootstrapApp(): void
	{
		// No longer needed - Filament handles authentication with ->default() and ->login()
		$this->info('✓ Bootstrap app ready (Filament handles auth)');
	}

	/**
	 * Rename tags migration to sequential format or remove if table already exists.
	 */
	protected function renameTagsMigration(): void
	{
		$migrationsPath = database_path('migrations');

		if (!File::isDirectory($migrationsPath))
		{
			return;
		}

		// Check if tags table already exists in database
		try
		{
			if (Schema::hasTable('tags'))
			{
				// Find all tags migration files (there might be duplicates)
				$tagsMigrations = collect(File::files($migrationsPath))
					->filter(function ($file) {
						return str_contains($file->getFilename(), 'create_tag_tables');
					});

				// If table exists, keep only the first migration and remove duplicates
				$firstMigration = $tagsMigrations->first();
				foreach ($tagsMigrations as $migration)
				{
					if ($migration->getPathname() !== $firstMigration->getPathname())
					{
						File::delete($migration->getPathname());
						$this->info("✓ Removed duplicate tags migration: {$migration->getFilename()}");
					}
				}

				return;
			}
		}
		catch (\Exception $e)
		{
			// Database might not be accessible, continue with rename logic
		}

		// Find the tags migration file
		$tagsMigration = collect(File::files($migrationsPath))
			->first(function ($file) {
				return str_contains($file->getFilename(), 'create_tag_tables');
			});

		if (!$tagsMigration)
		{
			return;
		}

		// Get all migration files to determine next sequential number
		$migrations = collect(File::files($migrationsPath))
			->map(fn ($file) => $file->getFilename())
			->filter(fn ($filename) => preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_/', $filename))
			->sort()
			->values();

		// Find the highest sequence number and date
		$maxSequence = 0;
		$lastDate = now()->format('Y_m_d');

		foreach ($migrations as $migration)
		{
			if (preg_match('/^(\d{4}_\d{2}_\d{2})_(\d{6})_/', $migration, $matches))
			{
				$date = $matches[1];
				$sequence = (int) $matches[2];

				if ($date === $lastDate)
				{
					$maxSequence = max($maxSequence, $sequence);
				}
				elseif ($date > $lastDate)
				{
					$lastDate = $date;
					$maxSequence = $sequence;
				}
			}
		}

		// Generate new sequential number (next after max)
		$newSequence = str_pad($maxSequence + 1, 6, '0', STR_PAD_LEFT);
		$newFilename = "{$lastDate}_{$newSequence}_create_tag_tables.php";

		// Rename the file
		$oldPath = $tagsMigration->getPathname();
		$newPath = $migrationsPath . '/' . $newFilename;

		if (File::exists($oldPath) && !File::exists($newPath))
		{
			File::move($oldPath, $newPath);
			$this->info("✓ Tags migration renamed to: {$newFilename}");
		}
	}

	/**
	 * Remove duplicate migrations (two_factor, teams, etc.).
	 */
	protected function removeDuplicateMigrations(): void
	{
		$migrationsPath = database_path('migrations');

		if (!File::isDirectory($migrationsPath))
		{
			return;
		}

		// Patterns to detect duplicate migrations
		$patterns = [
			'two_factor' => ['two_factor', 'two-factor'],
			'teams' => ['create_teams_table', 'teams_table'],
			'team_user' => ['create_team_user_table', 'team_user_table'],
			'team_invitations' => ['create_team_invitations_table', 'team_invitations_table'],
		];

		foreach ($patterns as $type => $searchTerms)
		{
			// Find all migrations matching this pattern
			$migrations = collect(File::files($migrationsPath))
				->filter(function ($file) use ($searchTerms) {
					$filename = $file->getFilename();
					foreach ($searchTerms as $term)
					{
						if (str_contains($filename, $term))
						{
							return true;
						}
					}
					return false;
				})
				->sortBy(function ($file) {
					return $file->getFilename();
				})
				->values();

			// If there are duplicates, keep only the first one
			if ($migrations->count() > 1)
			{
				$firstMigration = $migrations->first();

				foreach ($migrations as $migration)
				{
					if ($migration->getPathname() !== $firstMigration->getPathname())
					{
						File::delete($migration->getPathname());
						$this->info("✓ Removed duplicate {$type} migration: {$migration->getFilename()}");
					}
				}
			}
		}
	}
}
