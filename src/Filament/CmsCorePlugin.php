<?php

namespace Idoneo\CmsCore\Filament;

use Filament\Contracts\Plugin;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Idoneo\CmsCore\CmsCore;

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
			])
			->widgets([
				\Idoneo\CmsCore\Filament\Widgets\UserStatsOverview::class,
				\Idoneo\CmsCore\Filament\Widgets\UsersChart::class,
			]);
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
}
