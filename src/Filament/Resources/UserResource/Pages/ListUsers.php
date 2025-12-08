<?php

namespace Idoneo\CmsCore\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Idoneo\CmsCore\Filament\Resources\UserResource;

class ListUsers extends ListRecords
{
	protected static string $resource = UserResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\CreateAction::make(),
		];
	}
}

