<?php

namespace Idoneo\CmsCore\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Idoneo\CmsCore\Filament\Resources\UserResource;
use Laravel\Jetstream\Jetstream;

class EditUser extends EditRecord
{
	protected static string $resource = UserResource::class;

	protected function mutateFormDataBeforeFill(array $data): array
	{
		// Get default role from Jetstream
		$defaultRole = !empty(Jetstream::$roles) ? Jetstream::$roles[array_key_last(Jetstream::$roles)]->key : 'guest';

		// Load current role from team_user pivot table
		$user = $this->record;

		if ($user->currentTeam)
		{
			$membership = $user->currentTeam->users()
				->where('user_id', $user->id)
				->first();

			$data['role'] = $membership?->membership->role ?? $defaultRole;
		}

		return $data;
	}

	protected function mutateFormDataBeforeSave(array $data): array
	{
		// Get default role from Jetstream
		$defaultRole = !empty(Jetstream::$roles) ? Jetstream::$roles[array_key_last(Jetstream::$roles)]->key : 'guest';

		// Store role temporarily
		$this->cachedRole = $data['role'] ?? $defaultRole;

		// Remove role from data as it's not a User table column
		unset($data['role']);

		return $data;
	}

	protected function afterSave(): void
	{
		$user = $this->record;

		// Get default role from Jetstream
		$defaultRole = !empty(Jetstream::$roles) ? Jetstream::$roles[array_key_last(Jetstream::$roles)]->key : 'guest';

		// Update user's role in their current team
		if ($user->currentTeam)
		{
			$user->currentTeam->users()->updateExistingPivot($user->id, [
				'role' => $this->cachedRole ?? $defaultRole
			]);
		}
	}

	protected function getHeaderActions(): array
	{
		return [
			Actions\DeleteAction::make(),
		];
	}

	protected function getRedirectUrl(): string
	{
		return $this->getResource()::getUrl('index');
	}
}

