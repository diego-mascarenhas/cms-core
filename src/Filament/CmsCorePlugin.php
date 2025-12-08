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

		// Mi Perfil
		$items['profile'] = MenuItem::make()
			->label(__('Mi Perfil'))
			->url(fn (): string => route('profile.show'))
			->icon('heroicon-o-user');

		// Team-related items (only if teams are enabled)
		if (CmsCore::teamsEnabled())
		{
			// Ajustes del Equipo
			$items['team-settings'] = MenuItem::make()
				->label(__('Ajustes del Equipo'))
				->url(fn (): string => auth()->check() && auth()->user()->currentTeam
					? route('teams.show', auth()->user()->currentTeam)
					: '#'
				)
				->icon('heroicon-o-cog-6-tooth')
				->visible(fn (): bool => auth()->check() && auth()->user()->currentTeam !== null);

			// Crear Nuevo Equipo
			$items['create-team'] = MenuItem::make()
				->label(__('Crear Nuevo Equipo'))
				->url(fn (): string => route('teams.create'))
				->icon('heroicon-o-plus-circle');
		}

		return $items;
	}
}
