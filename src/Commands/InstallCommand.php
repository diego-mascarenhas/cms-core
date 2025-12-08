<?php

namespace Idoneo\CmsCore\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

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

		// Update web routes
		$this->updateWebRoutes();

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
		$this->line('  2. Add APP_TEAMS=true to .env to enable teams feature');
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

		// Add use statement
		if (!str_contains($content, 'use Idoneo\CmsCore\Filament\CmsCorePlugin;'))
		{
			$content = str_replace(
				'use Filament\Widgets\FilamentInfoWidget;',
				"use Filament\Widgets\FilamentInfoWidget;\nuse Idoneo\CmsCore\Filament\CmsCorePlugin;",
				$content
			);
		}

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

		file_put_contents($userModelPath, $content);
		$this->info('✓ User model updated with FilamentUser and HasRoles');
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
}
