<?php

namespace Idoneo\CmsCore\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Idoneo\CmsCore\CmsCore;

class DiagnoseCommand extends Command
{
	protected $signature = 'cms-core:diagnose';

	protected $description = 'Diagnose CMS-Core configuration and setup';

	public function handle(): int
	{
		$this->info('CMS-Core Diagnostics');
		$this->newLine();

		// Check config file
		$this->checkConfigFile();

		// Check teams configuration
		$this->checkTeamsConfig();

		// Check Jetstream routes
		$this->checkJetstreamRoutes();

		// Check user and team
		$this->checkCurrentUser();

		$this->newLine();
		$this->info('✓ Diagnostics complete');

		return self::SUCCESS;
	}

	protected function checkConfigFile(): void
	{
		$this->comment('1. Configuration File:');

		$configPath = config_path('cms-core.php');
		if (file_exists($configPath))
		{
			$this->line('  ✓ Config file exists: ' . $configPath);
		}
		else
		{
			$this->error('  ✗ Config file NOT found: ' . $configPath);
			$this->warn('  Run: php artisan vendor:publish --tag=cms-core-config --force');
		}

		$this->newLine();
	}

	protected function checkTeamsConfig(): void
	{
		$this->comment('2. Teams Configuration:');

		// Check env variable
		$envValue = env('APP_TEAMS');
		$this->line('  ENV APP_TEAMS: ' . ($envValue ? 'true' : 'false or not set'));

		// Check config value
		$configValue = config('cms-core.teams_enabled');
		$this->line('  Config cms-core.teams_enabled: ' . ($configValue ? 'true' : 'false'));

		// Check CmsCore helper
		$helperValue = CmsCore::teamsEnabled();
		$this->line('  CmsCore::teamsEnabled(): ' . ($helperValue ? 'true' : 'false'));

		if (!$helperValue && $envValue)
		{
			$this->newLine();
			$this->error('  ✗ MISMATCH: ENV is true but config returns false');
			$this->warn('  Solutions:');
			$this->line('    1. Run: php artisan config:clear');
			$this->line('    2. Verify .env has: APP_TEAMS=true (no spaces)');
			$this->line('    3. Restart server if using php artisan serve');
		}
		elseif ($helperValue)
		{
			$this->line('  ✓ Teams feature is ENABLED');
		}
		else
		{
			$this->line('  ✗ Teams feature is DISABLED');
		}

		$this->newLine();
	}

	protected function checkJetstreamRoutes(): void
	{
		$this->comment('3. Jetstream Routes:');

		$routes = [
			'profile.show' => 'Profile page',
			'teams.show' => 'Team settings',
			'teams.create' => 'Create team',
		];

		$allExist = true;
		foreach ($routes as $routeName => $description)
		{
			if (Route::has($routeName))
			{
				$this->line("  ✓ Route '{$routeName}' exists ({$description})");
			}
			else
			{
				$this->error("  ✗ Route '{$routeName}' NOT found ({$description})");
				$allExist = false;
			}
		}

		if (!$allExist)
		{
			$this->newLine();
			$this->warn('  Jetstream routes are missing. Check routes/web.php:');
			$this->line('  - Should have: use Laravel\Jetstream\Jetstream;');
			$this->line('  - Should have: Jetstream::routes();');
		}

		$this->newLine();
	}

	protected function checkCurrentUser(): void
	{
		$this->comment('4. Current User (if authenticated):');

		if (!auth()->check())
		{
			$this->line('  ℹ No user authenticated (run from CLI)');
			return;
		}

		$user = auth()->user();
		$this->line('  ✓ User authenticated: ' . $user->email);

		if (method_exists($user, 'currentTeam'))
		{
			$team = $user->currentTeam;
			if ($team)
			{
				$this->line('  ✓ Current team: ' . $team->name . ' (ID: ' . $team->id . ')');
			}
			else
			{
				$this->warn('  ✗ No current team set');
			}
		}
		else
		{
			$this->error('  ✗ User model does not have currentTeam relationship');
		}

		$this->newLine();
	}
}
