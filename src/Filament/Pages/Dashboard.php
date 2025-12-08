<?php

namespace Idoneo\CmsCore\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
	protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

	public function getHeading(): ?string
	{
		return '';
	}

	public static function getNavigationLabel(): string
	{
		return __('Dashboard');
	}

	public function getTitle(): string
	{
		return '';
	}

	public function getHeaderWidgets(): array
	{
		return [];
	}

	public function getWidgets(): array
	{
		return [
			\Idoneo\CmsCore\Filament\Widgets\UserStatsOverview::class,
			\Idoneo\CmsCore\Filament\Widgets\UsersChart::class,
		];
	}
}
