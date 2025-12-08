<?php

namespace Idoneo\CmsCore;

use Idoneo\CmsCore\Commands\InstallCommand;
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

			// Translations
			$this->publishes([
				__DIR__ . '/../resources/lang' => $this->app->langPath('vendor/cms-core'),
			], 'cms-core-lang');

			// All publishables
			$this->publishes([
				__DIR__ . '/../config/cms-core.php' => config_path('cms-core.php'),
				__DIR__ . '/../resources/views' => resource_path('views/vendor/cms-core'),
				__DIR__ . '/../resources/lang' => $this->app->langPath('vendor/cms-core'),
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
	}

	/**
	 * Register Livewire components.
	 */
	protected function registerLivewireComponents(): void
	{
		Livewire::component('cms-core::team-switcher', \Idoneo\CmsCore\Livewire\TeamSwitcher::class);
	}
}

