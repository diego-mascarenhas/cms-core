<?php

namespace Idoneo\CmsCore;

class CmsCore
{
	/**
	 * Check if teams feature is enabled.
	 */
	public static function teamsEnabled(): bool
	{
		return config('cms-core.teams_enabled', false);
	}

	/**
	 * Get the team model class.
	 */
	public static function teamModel(): string
	{
		return config('cms-core.team_model', \App\Models\Team::class);
	}

	/**
	 * Get the user model class.
	 */
	public static function userModel(): string
	{
		return config('cms-core.user_model', \App\Models\User::class);
	}
}

