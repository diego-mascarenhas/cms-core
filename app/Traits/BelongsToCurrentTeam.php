<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToCurrentTeam
{
	/**
	 * Boot the trait and add the global scope.
	 */
	protected static function bootBelongsToCurrentTeam(): void
	{
		static::addGlobalScope('current_team', function (Builder $builder)
		{
			if (auth()->check() && auth()->user()->current_team_id)
			{
				$builder->where('team_id', auth()->user()->current_team_id);
			}
		});

		static::creating(function ($model)
		{
			if (auth()->check() && auth()->user()->current_team_id && empty($model->team_id))
			{
				$model->team_id = auth()->user()->current_team_id;
			}
		});
	}

	/**
	 * Scope to filter by a specific team.
	 */
	public function scopeForTeam(Builder $query, int $teamId): Builder
	{
		return $query->withoutGlobalScope('current_team')->where('team_id', $teamId);
	}

	/**
	 * Scope to get records for all teams (bypass scope).
	 */
	public function scopeWithoutTeamScope(Builder $query): Builder
	{
		return $query->withoutGlobalScope('current_team');
	}

	/**
	 * Get the team that owns this model.
	 */
	public function team()
	{
		return $this->belongsTo(\App\Models\Team::class);
	}
}



