<?php

namespace Idoneo\CmsCore\Filament\Resources\UserResource\Pages;

use App\Models\Team;
use Filament\Resources\Pages\CreateRecord;
use Idoneo\CmsCore\Filament\Resources\UserResource;
use Laravel\Jetstream\Jetstream;

class CreateUser extends CreateRecord
{
	protected static string $resource = UserResource::class;

	protected function mutateFormDataBeforeFill(array $data): array
	{
		// Set default role when form is initialized
		$data['role'] = $data['role'] ?? 'member';

		return $data;
	}

	protected function mutateFormDataBeforeCreate(array $data): array
	{
		// Store role temporarily, will be assigned after team creation
		$this->cachedRole = $data['role'] ?? 'member';

		// Remove role from data as it's not a User table column
		unset($data['role']);

		return $data;
	}

	protected function afterCreate(): void
	{
		$user = $this->record;

		// Create personal team if doesn't exist
		if (!$user->currentTeam)
		{
			$team = Team::create([
				'name' => $user->name . "'s Team",
				'user_id' => $user->id,
				'personal_team' => true,
			]);

			$user->current_team_id = $team->id;
			$user->save();
		}

		// Attach user to their team with the selected role
		if ($user->currentTeam)
		{
			$user->currentTeam->users()->syncWithoutDetaching([
				$user->id => ['role' => $this->cachedRole ?? 'member']
			]);
		}
	}

	protected function getRedirectUrl(): string
	{
		return $this->getResource()::getUrl('index');
	}
}

