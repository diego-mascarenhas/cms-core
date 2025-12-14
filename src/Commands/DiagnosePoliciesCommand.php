<?php

namespace Idoneo\CmsCore\Commands;

use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Gate;

class DiagnosePoliciesCommand extends Command
{
	protected $signature = 'cms-core:diagnose-policies';

	protected $description = 'Diagnose policy registration and permissions';

	public function handle(): int
	{
		$this->info('CMS-Core Policies Diagnostic');
		$this->newLine();

		// Check if AuthServiceProvider is registered
		$this->info('1. Checking AuthServiceProvider registration...');
		$providersPath = base_path('bootstrap/providers.php');

		if (!file_exists($providersPath))
		{
			$this->error('   ✗ bootstrap/providers.php not found');
			return self::FAILURE;
		}

		$content = file_get_contents($providersPath);
		$authProviderRegistered = str_contains($content, 'AuthServiceProvider');

		if ($authProviderRegistered)
		{
			$this->info('   ✓ AuthServiceProvider is registered in bootstrap/providers.php');
		}
		else
		{
			$this->error('   ✗ AuthServiceProvider NOT registered in bootstrap/providers.php');
			$this->warn('   Run: php artisan cms-core:update --force');
		}

		$this->newLine();

		// Check if policy files exist
		$this->info('2. Checking policy files...');

		$postPolicyPath = app_path('Policies/PostPolicy.php');
		$userPolicyPath = app_path('Policies/UserPolicy.php');

		if (file_exists($postPolicyPath))
		{
			$this->info('   ✓ PostPolicy.php exists');
		}
		else
		{
			$this->error('   ✗ PostPolicy.php NOT found');
		}

		if (file_exists($userPolicyPath))
		{
			$this->info('   ✓ UserPolicy.php exists');
		}
		else
		{
			$this->error('   ✗ UserPolicy.php NOT found');
		}

		$this->newLine();

		// Check if policies are registered in Gate
		$this->info('3. Checking policy registration in Gate...');

		$userPolicyRegistered = Gate::getPolicyFor(User::class) !== null;
		$postPolicyRegistered = Gate::getPolicyFor(Post::class) !== null;

		if ($userPolicyRegistered)
		{
			$policyClass = get_class(Gate::getPolicyFor(User::class));
			$this->info("   ✓ UserPolicy registered: {$policyClass}");
		}
		else
		{
			$this->error('   ✗ UserPolicy NOT registered');
		}

		if ($postPolicyRegistered)
		{
			$policyClass = get_class(Gate::getPolicyFor(Post::class));
			$this->info("   ✓ PostPolicy registered: {$policyClass}");
		}
		else
		{
			$this->error('   ✗ PostPolicy NOT registered');
		}

		$this->newLine();

		// Test current user permissions
		if (auth()->check())
		{
			$user = auth()->user();
			$this->info('4. Testing current user permissions...');
			$this->info("   User: {$user->name} ({$user->email})");

			if ($user->currentTeam)
			{
				$membership = $user->currentTeam->users()
					->where('user_id', $user->id)
					->first();
				$role = $membership?->membership->role ?? 'unknown';
				$this->info("   Role: {$role}");
			}
			else
			{
				$this->warn('   No current team');
			}

			$this->newLine();

			// Test UserPolicy
			$canViewUsers = $user->can('viewAny', User::class);
			$canCreateUsers = $user->can('create', User::class);

			$this->info("   Can view users: " . ($canViewUsers ? '✓ YES' : '✗ NO'));
			$this->info("   Can create users: " . ($canCreateUsers ? '✓ YES' : '✗ NO'));

			$this->newLine();

			// Test PostPolicy
			$canViewPosts = $user->can('viewAny', Post::class);
			$canCreatePosts = $user->can('create', Post::class);

			$this->info("   Can view posts: " . ($canViewPosts ? '✓ YES' : '✗ NO'));
			$this->info("   Can create posts: " . ($canCreatePosts ? '✓ YES' : '✗ NO'));
		}
		else
		{
			$this->warn('4. No authenticated user to test permissions');
		}

		$this->newLine();

		// Summary
		$allGood = $authProviderRegistered &&
				   file_exists($postPolicyPath) &&
				   file_exists($userPolicyPath) &&
				   $userPolicyRegistered &&
				   $postPolicyRegistered;

		if ($allGood)
		{
			$this->info('✓ All checks passed!');
			return self::SUCCESS;
		}
		else
		{
			$this->error('✗ Some checks failed. Run: php artisan cms-core:update --force');
			return self::FAILURE;
		}
	}
}
