<?php

namespace Idoneo\CmsCore;

use Idoneo\CmsCore\Commands\CreateAdminCommand;
use Idoneo\CmsCore\Commands\GenerateApiToken;
use Idoneo\CmsCore\Commands\InstallCommand;
use Idoneo\CmsCore\Commands\UpdateCommand;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class CmsCoreServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 */
	public function register(): void
	{
		$this->mergeConfigFrom(
			__DIR__ . '/../config/cms-core.php',
			'cms-core'
		);

		$this->app->singleton(CmsCore::class, fn () => new CmsCore());
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void
	{
		$this->registerCommands();
		$this->registerPublishables();
		$this->registerViews();
		$this->registerTranslations();
		$this->registerLivewireComponents();
	}

	/**
	 * Register console commands.
	 */
	protected function registerCommands(): void
	{
		if ($this->app->runningInConsole())
		{
			$this->commands([
				InstallCommand::class,
				UpdateCommand::class,
				CreateAdminCommand::class,
				GenerateApiToken::class,
			]);
		}
	}

	/**
	 * Register publishable resources.
	 */
	protected function registerPublishables(): void
	{
		if ($this->app->runningInConsole())
		{
			// Config
			$this->publishes([
				__DIR__ . '/../config/cms-core.php' => config_path('cms-core.php'),
			], 'cms-core-config');

			// Views
			$this->publishes([
				__DIR__ . '/../resources/views' => resource_path('views/vendor/cms-core'),
			], 'cms-core-views');

		// Translations (JSON files)
		$this->publishes([
			__DIR__ . '/../resources/lang/es.json' => $this->app->langPath('es.json'),
		], 'cms-core-lang');

		// Validation translations (PHP files)
		$this->publishes([
			__DIR__ . '/../resources/lang/es' => $this->app->langPath('es'),
		], 'cms-core-lang');

		// Migrations
		$this->publishes([
			__DIR__ . '/../database/migrations' => database_path('migrations'),
		], 'cms-core-migrations');

		// Seeders
		$this->publishes([
			__DIR__ . '/../database/seeders/DatabaseSeeder.php' => database_path('seeders/DatabaseSeeder.php'),
		], 'cms-core-seeders');

		// Models
		$this->publishes([
			__DIR__ . '/../app/Models/Post.php' => app_path('Models/Post.php'),
		], 'cms-core-models');

		// API Controllers, Requests, Resources, Middleware
		$this->publishes([
			__DIR__ . '/../app/Http/Controllers/Api' => app_path('Http/Controllers/Api'),
			__DIR__ . '/../app/Http/Requests/Api' => app_path('Http/Requests/Api'),
			__DIR__ . '/../app/Http/Resources' => app_path('Http/Resources'),
			__DIR__ . '/../app/Http/Middleware/AuthenticateApiToken.php' => app_path('Http/Middleware/AuthenticateApiToken.php'),
		], 'cms-core-api');

		// All publishables
		$this->publishes([
			__DIR__ . '/../config/cms-core.php' => config_path('cms-core.php'),
			__DIR__ . '/../resources/views' => resource_path('views/vendor/cms-core'),
			__DIR__ . '/../resources/lang/es.json' => $this->app->langPath('es.json'),
			__DIR__ . '/../resources/lang/es' => $this->app->langPath('es'),
			__DIR__ . '/../database/migrations' => database_path('migrations'),
			__DIR__ . '/../database/seeders/DatabaseSeeder.php' => database_path('seeders/DatabaseSeeder.php'),
		], 'cms-core');
		}
	}

	/**
	 * Register package views.
	 */
	protected function registerViews(): void
	{
		$this->loadViewsFrom(__DIR__ . '/../resources/views', 'cms-core');
	}

	/**
	 * Register package translations.
	 */
	protected function registerTranslations(): void
	{
		$this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
		$this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'cms-core');
	}

	/**
	 * Register Livewire components.
	 */
	protected function registerLivewireComponents(): void
	{
		Livewire::component('cms-core::team-switcher', \Idoneo\CmsCore\Livewire\TeamSwitcher::class);
	}
}

