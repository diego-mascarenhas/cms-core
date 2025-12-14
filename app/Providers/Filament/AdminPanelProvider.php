<?php

namespace App\Providers\Filament;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Idoneo\CmsCore\Filament\CmsCorePlugin;
use Idoneo\CmsCore\Filament\Pages\Dashboard;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->sidebarWidth('15rem')
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->plugin(CmsCorePlugin::make())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
			->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
			->pages([
				\Idoneo\CmsCore\Filament\Pages\Dashboard::class,
			])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // Dashboard widgets are registered by CmsCorePlugin
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

        // Auto-configure logos if they exist
        $this->configureBrandLogos($panel);

        return $panel;
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
		if (\Route::has('profile.show')) {
			$items['profile'] = MenuItem::make()
				->label('Mi Perfil')
				->url(fn (): string => route('profile.show'))
				->icon('heroicon-o-user');
		}

		// Team-related items (only if teams are enabled)
		if ($this->teamsEnabled())
		{
			// Ajustes del Equipo
			if (\Route::has('teams.show')) {
				$items['team-settings'] = MenuItem::make()
					->label('Ajustes del Equipo')
					->url(fn (): string => auth()->check() && auth()->user()->currentTeam
						? route('teams.show', auth()->user()->currentTeam)
						: '#'
					)
					->icon('heroicon-o-cog-6-tooth')
					->visible(fn (): bool => auth()->check() && auth()->user()->currentTeam !== null);
			}

			// Crear Nuevo Equipo
			if (\Route::has('teams.create')) {
				$items['create-team'] = MenuItem::make()
					->label('Crear Nuevo Equipo')
					->url(fn (): string => route('teams.create'))
					->icon('heroicon-o-plus-circle');
			}
		}

		// Don't customize logout - let Filament handle it with default behavior
		// Filament will add the logout item automatically

		return $items;
	}

    /**
     * Automatically configure brand logos if logo-light.svg or logo-dark.svg exist.
     */
    protected function configureBrandLogos(Panel $panel): void
    {
        $publicPath = public_path();
        $logoLightPath = $publicPath . '/logo-light.svg';
        $logoDarkPath = $publicPath . '/logo-dark.svg';

        $hasLogoLight = File::exists($logoLightPath);
        $hasLogoDark = File::exists($logoDarkPath);

        if ($hasLogoLight)
        {
            $panel->brandLogo(asset('logo-light.svg'));
        }

        if ($hasLogoDark)
        {
            $panel->darkModeBrandLogo(asset('logo-dark.svg'));
        }
    }
}
