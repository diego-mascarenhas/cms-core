<?php

namespace Idoneo\CmsCore\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class UpdateCommand extends Command
{
	protected $signature = 'cms-core:update
		{--migrations : Only publish migrations}
		{--force : Force publish even if files exist}';

	protected $description = 'Update CMS-Core package resources (migrations, config, views, translations)';

	public function handle(): int
	{
		$this->info('Updating CMS-Core resources...');
		$this->newLine();

		$force = $this->option('force');
		$migrationsOnly = $this->option('migrations');

		if (!$migrationsOnly)
		{
			// Publish config
			$this->call('vendor:publish', [
				'--tag' => 'cms-core-config',
				'--force' => $force,
			]);
			$this->info('✓ Config updated');

			// Publish views
			$this->call('vendor:publish', [
				'--tag' => 'cms-core-views',
				'--force' => $force,
			]);
			$this->info('✓ Views updated');

			// Publish translations
			$this->call('vendor:publish', [
				'--tag' => 'cms-core-lang',
				'--force' => $force,
			]);
			$this->info('✓ Translations updated');
		}

		// Publish Spatie Permission migrations (if not already published)
		$this->call('vendor:publish', [
			'--provider' => 'Spatie\Permission\PermissionServiceProvider',
		]);
		$this->info('✓ Permission migrations checked');

		// Publish Spatie Tags migrations
		$this->call('vendor:publish', [
			'--provider' => 'Spatie\Tags\TagsServiceProvider',
			'--tag' => 'tags-migrations',
		]);
		$this->info('✓ Tags migrations checked');

		// Publish Spatie Media Library migrations
		$this->call('vendor:publish', [
			'--provider' => 'Spatie\MediaLibrary\MediaLibraryServiceProvider',
			'--tag' => 'medialibrary-migrations',
		]);
		$this->info('✓ Media Library migrations checked');

		// Rename tags migration to sequential format
		$this->renameTagsMigration();

		// Remove duplicate migrations (two_factor, teams, etc.)
		$this->removeDuplicateMigrations();

		// Publish CMS Core migrations
		$this->call('vendor:publish', [
			'--tag' => 'cms-core-migrations',
			'--force' => $force,
		]);
		$this->info('✓ CMS Core migrations updated');

		// Publish models
		$this->call('vendor:publish', [
			'--tag' => 'cms-core-models',
			'--force' => $force,
		]);
		$this->info('✓ Models updated');

		// Publish API files (controllers, requests, resources, middleware)
		$this->call('vendor:publish', [
			'--tag' => 'cms-core-api',
			'--force' => $force,
		]);
		$this->info('✓ API files updated');

		// Register API routes
		$this->registerApiRoutes();

		$this->newLine();
		$this->info('CMS-Core resources updated successfully!');
		$this->newLine();
		$this->comment('Run "php artisan migrate" to apply new migrations (if any).');

		return self::SUCCESS;
	}

	/**
	 * Register API routes in routes/api.php.
	 */
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
		if (str_contains($content, 'PostController') && str_contains($content, '/posts'))
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
