<?php

namespace App\Providers\Filament;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
	public function panel(Panel $panel): Panel
	{
		return $panel
			->default()
			->id('admin')
			->path('admin')
			->login()
			->colors([
				'primary' => Color::Amber,
			])
			->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
			->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
			->pages([
				Pages\Dashboard::class,
			])
			->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
			->widgets([
				Widgets\AccountWidget::class,
				Widgets\FilamentInfoWidget::class,
			])
			->middleware([
				EncryptCookies::class,
				AddQueuedCookiesToResponse::class,
				StartSession::class,
				AuthenticateSession::class,
				ShareErrorsFromSession::class,
				VerifyCsrfToken::class,
				SubstituteBindings::class,
				DisableBladeIconComponents::class,
				DispatchServingFilamentEvent::class,
			])
			->authMiddleware([
				Authenticate::class,
			])
			->userMenuItems($this->getUserMenuItems());
	}

	/**
	 * Check if teams feature is enabled.
	 */
	protected function teamsEnabled(): bool
	{
		return config('cms.teams_enabled', true);
	}

	/**
	 * Get custom user menu items.
	 */
	protected function getUserMenuItems(): array
	{
		$items = [];

		// Mi Perfil
		$items['profile'] = MenuItem::make()
			->label('Mi Perfil')
			->url(fn (): string => route('profile.show'))
			->icon('heroicon-o-user');

		// Team-related items (only if teams are enabled)
		if ($this->teamsEnabled())
		{
			// Ajustes del Equipo
			$items['team-settings'] = MenuItem::make()
				->label('Ajustes del Equipo')
				->url(fn (): string => auth()->check() && auth()->user()->currentTeam
					? route('teams.show', auth()->user()->currentTeam)
					: '#'
				)
				->icon('heroicon-o-cog-6-tooth')
				->visible(fn (): bool => auth()->check() && auth()->user()->currentTeam !== null);

			// Crear Nuevo Equipo
			$items['create-team'] = MenuItem::make()
				->label('Crear Nuevo Equipo')
				->url(fn (): string => route('teams.create'))
				->icon('heroicon-o-plus-circle');
		}

		// Logout - always at the end with icon
		$items['logout'] = MenuItem::make()
			->label('Salir')
			->url(fn (): string => Filament::getLogoutUrl())
			->icon('heroicon-o-arrow-right-on-rectangle')
			->postAction(fn (): string => Filament::getLogoutUrl());

		return $items;
	}
}
