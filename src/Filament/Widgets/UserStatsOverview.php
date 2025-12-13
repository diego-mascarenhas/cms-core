<?php

namespace Idoneo\CmsCore\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Jetstream;

class UserStatsOverview extends BaseWidget
{
	protected function getStats(): array
	{
		$stats = [];
		$userModel = config('auth.providers.users.model', 'App\\Models\\User');

		// Total Users
		$totalUsers = $userModel::count();
		$stats[] = Stat::make(__('Total Users'), $totalUsers)
			->description(__('Registered users in the system'))
			->descriptionIcon('heroicon-m-users')
			->color('primary')
			->chart($this->getUsersChartData());

		// Verified Users
		$verifiedUsers = $userModel::whereNotNull('email_verified_at')->count();
		$verificationRate = $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100) : 0;
		$stats[] = Stat::make(__('Verified Users'), $verifiedUsers)
			->description("{$verificationRate}% " . __('verified'))
			->descriptionIcon('heroicon-m-check-badge')
			->color('success');

		// New Users This Month
		$newUsersThisMonth = $userModel::whereMonth('created_at', now()->month)
			->whereYear('created_at', now()->year)
			->count();
		$stats[] = Stat::make(__('New This Month'), $newUsersThisMonth)
			->description(__('Users registered this month'))
			->descriptionIcon('heroicon-m-arrow-trending-up')
			->color('info');

		// Users by Role (if Jetstream roles are configured)
		if (!empty(Jetstream::$roles))
		{
			foreach (Jetstream::$roles as $role)
			{
				$roleCount = $this->getUsersByRole($role->key);
				$stats[] = Stat::make(ucfirst($role->name), $roleCount)
					->description(__('Role') . ": {$role->name}")
					->descriptionIcon('heroicon-m-user-circle')
					->color($this->getRoleColor($role->key));
			}
		}

		return $stats;
	}

	/**
	 * Get users count by role.
	 */
	protected function getUsersByRole(string $roleKey): int
	{
		return DB::table('team_user')
			->where('role', $roleKey)
			->distinct('user_id')
			->count('user_id');
	}

	/**
	 * Get color for role badge.
	 */
	protected function getRoleColor(string $roleKey): string
	{
		return match ($roleKey)
		{
			'admin' => 'success',
			'member' => 'info',
			'guest' => 'warning',
			default => 'gray',
		};
	}

	/**
	 * Get chart data for users growth.
	 */
	protected function getUsersChartData(): array
	{
		$userModel = config('auth.providers.users.model', 'App\\Models\\User');
		$data = [];
		for ($i = 6; $i >= 0; $i--)
		{
			$date = now()->subDays($i);
			$count = $userModel::whereDate('created_at', $date->toDateString())->count();
			$data[] = $count;
		}

		return $data;
	}
}
