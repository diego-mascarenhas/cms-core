<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
	use HandlesAuthorization;

	/**
	 * Determine whether the user can view any models.
	 */
	public function viewAny(User $user): bool
	{
		// Only admins can view user list
		return $this->isAdmin($user);
	}

	/**
	 * Determine whether the user can view the model.
	 */
	public function view(User $user, User $model): bool
	{
		// Only admins can view user details
		return $this->isAdmin($user);
	}

	/**
	 * Determine whether the user can create models.
	 */
	public function create(User $user): bool
	{
		// Only admins can create users
		return $this->isAdmin($user);
	}

	/**
	 * Determine whether the user can update the model.
	 */
	public function update(User $user, User $model): bool
	{
		// Only admins can update users
		return $this->isAdmin($user);
	}

	/**
	 * Determine whether the user can delete the model.
	 */
	public function delete(User $user, User $model): bool
	{
		// Only admins can delete users
		return $this->isAdmin($user);
	}

	/**
	 * Determine whether the user can restore the model.
	 */
	public function restore(User $user, User $model): bool
	{
		// Only admins can restore users
		return $this->isAdmin($user);
	}

	/**
	 * Determine whether the user can permanently delete the model.
	 */
	public function forceDelete(User $user, User $model): bool
	{
		// Only admins can force delete users
		return $this->isAdmin($user);
	}

	/**
	 * Check if the user is an admin in their current team.
	 */
	protected function isAdmin(User $user): bool
	{
		if (!$user->currentTeam)
		{
			return false;
		}

		$membership = $user->currentTeam->users()
			->where('user_id', $user->id)
			->first();

		return $membership?->membership->role === 'admin';
	}
}
