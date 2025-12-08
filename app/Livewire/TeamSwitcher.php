<?php

namespace App\Livewire;

use Livewire\Component;

class TeamSwitcher extends Component
{
	/**
	 * Switch to the specified team.
	 */
	public function switchTeam(int $teamId): void
	{
		$user = auth()->user();
		$team = $user->allTeams()->find($teamId);

		if ($team)
		{
			$user->switchTeam($team);
			$this->redirect(request()->header('Referer', '/admin'), navigate: true);
		}
	}

	/**
	 * Get all teams sorted alphabetically.
	 */
	public function getTeamsProperty(): \Illuminate\Support\Collection
	{
		return auth()->user()->allTeams()->sortBy('name');
	}

	/**
	 * Get current team ID.
	 */
	public function getCurrentTeamIdProperty(): ?int
	{
		return auth()->user()->currentTeam?->id;
	}

	public function render()
	{
		return view('livewire.team-switcher');
	}
}


