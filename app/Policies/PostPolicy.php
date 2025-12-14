<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
	use HandlesAuthorization;

	/**
	 * Determine whether the user can view any models.
	 */
	public function viewAny(User $user): bool
	{
		return true;
	}

	/**
	 * Determine whether the user can view the model.
	 */
	public function view(User $user, Post $post): bool
	{
		return true;
	}

	/**
	 * Determine whether the user can create models.
	 */
	public function create(User $user): bool
	{
		// All authenticated users can create posts
		return true;
	}

	/**
	 * Determine whether the user can update the model.
	 */
	public function update(User $user, Post $post): bool
	{
		// Admin can edit all posts, members can only edit their own
		if ($this->isAdmin($user))
		{
			return true;
		}

		return $post->user_id === $user->id;
	}

	/**
	 * Determine whether the user can delete the model.
	 */
	public function delete(User $user, Post $post): bool
	{
		// Admin can delete all posts, members can only delete their own
		if ($this->isAdmin($user))
		{
			return true;
		}

		return $post->user_id === $user->id;
	}

	/**
	 * Determine whether the user can restore the model.
	 */
	public function restore(User $user, Post $post): bool
	{
		// Admin can restore all posts, members can only restore their own
		if ($this->isAdmin($user))
		{
			return true;
		}

		return $post->user_id === $user->id;
	}

	/**
	 * Determine whether the user can permanently delete the model.
	 */
	public function forceDelete(User $user, Post $post): bool
	{
		// Admin can force delete all posts, members can only force delete their own
		if ($this->isAdmin($user))
		{
			return true;
		}

		return $post->user_id === $user->id;
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
