<?php

namespace Idoneo\CmsCore\Filament;

use Filament\Contracts\Plugin;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Idoneo\CmsCore\CmsCore;
use Illuminate\Support\Facades\File;

class CmsCorePlugin implements Plugin
{
	public function getId(): string
	{
		return 'cms-core';
	}

	public function register(Panel $panel): void
	{
		$panel
			->userMenuItems($this->getUserMenuItems())
			->resources([
				\Idoneo\CmsCore\Filament\Resources\UserResource::class,
				\Idoneo\CmsCore\Filament\Resources\PostResource::class,
			])
			->widgets([
				\Idoneo\CmsCore\Filament\Widgets\UserStatsOverview::class,
				\Idoneo\CmsCore\Filament\Widgets\UsersChart::class,
				\Idoneo\CmsCore\Filament\Widgets\PostsChart::class,
			]);

		// Auto-configure logos if they exist
		$this->configureBrandLogos($panel);
	}

	public function boot(Panel $panel): void
	{
		//
	}

	public static function make(): static
	{
		return app(static::class);
	}

	public static function get(): static
	{
		/** @var static $plugin */
		$plugin = filament(app(static::class)->getId());

		return $plugin;
	}

	/**
	 * Get custom user menu items.
	 */
	protected function getUserMenuItems(): array
	{
		$items = [];

		// Team-related items (only if teams are enabled)
		if (CmsCore::teamsEnabled())
		{
			// Profile
			$items['profile'] = MenuItem::make()
				->label(__('Profile'))
				->url(fn (): string => route('profile.show'))
				->icon('heroicon-o-user');

			// Team Settings
			$items['team-settings'] = MenuItem::make()
				->label(__('Team Settings'))
				->url(fn (): string => auth()->check() && auth()->user()->currentTeam
					? route('teams.show', auth()->user()->currentTeam)
					: '#'
				)
				->icon('heroicon-o-cog-6-tooth')
				->visible(fn (): bool => auth()->check() && auth()->user()->currentTeam !== null);

			// Create New Team
			$items['create-team'] = MenuItem::make()
				->label(__('Create New Team'))
				->url(fn (): string => route('teams.create'))
				->icon('heroicon-o-plus-circle');
		}

		return $items;
	}

	/**
	 * Automatically configure brand logos if logo-*.svg files exist in public/custom/.
	 */
	protected function configureBrandLogos(Panel $panel): void
	{
		$customPath = public_path('custom');

		if (!File::isDirectory($customPath))
		{
			return;
		}

		$logoFiles = File::glob($customPath . '/logo-*.svg');

		if (empty($logoFiles))
		{
			return;
		}

		// Look for logo-light.svg and logo-dark.svg
		$logoLight = null;
		$logoDark = null;

		foreach ($logoFiles as $logoFile)
		{
			$filename = basename($logoFile);

			if ($filename === 'logo-light.svg')
			{
				$logoLight = 'custom/' . $filename;
			}
			elseif ($filename === 'logo-dark.svg')
			{
				$logoDark = 'custom/' . $filename;
			}
		}

		// Configure logos
		if ($logoLight)
		{
			$panel->brandLogo(asset($logoLight));
		}

		if ($logoDark)
		{
			$panel->darkModeBrandLogo(asset($logoDark));
		}
	}
}
