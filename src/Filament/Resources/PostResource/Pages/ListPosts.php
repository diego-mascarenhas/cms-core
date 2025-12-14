<?php

namespace Idoneo\CmsCore\Filament\Resources\PostResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Idoneo\CmsCore\Filament\Resources\PostResource;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
	protected static string $resource = PostResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\CreateAction::make(),
		];
	}

	protected function getTableQuery(): ?Builder
	{
		$query = parent::getTableQuery();
		$user = auth()->user();

		if (!$user)
		{
			return $query->whereRaw('1 = 0'); // Return no results if not authenticated
		}

		// Check if user is admin
		if ($user->currentTeam)
		{
			$membership = $user->currentTeam->users()
				->where('user_id', $user->id)
				->first();

			// If admin, show all posts
			if ($membership?->membership->role === 'admin')
			{
				return $query;
			}
		}

		// If not admin, only show user's own posts
		return $query->where('user_id', $user->id);
	}
}
