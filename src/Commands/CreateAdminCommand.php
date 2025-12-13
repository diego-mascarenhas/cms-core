<?php

namespace Idoneo\CmsCore\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
	protected $signature = 'cms-core:create-admin
		{--email=hola@humano.app : Admin user email}
		{--password=Simplicity! : Admin user password}
		{--name=Admin : Admin user name}';

	protected $description = 'Create an admin user with personal team';

	public function handle(): int
	{
		$userModel = config('cms-core.user_model', \App\Models\User::class);
		$teamModel = config('cms-core.team_model', \App\Models\Team::class);

		$email = $this->option('email');
		$password = $this->option('password');
		$name = $this->option('name');

		// Check if user already exists
		if ($userModel::where('email', $email)->exists())
		{
			$this->error("User with email {$email} already exists!");
			return self::FAILURE;
		}

		// Create user
		$user = $userModel::create([
			'name' => $name,
			'email' => $email,
			'password' => Hash::make($password),
			'email_verified_at' => now(),
		]);

		// Create personal team (Jetstream requires it)
		if (method_exists($user, 'ownedTeams') && class_exists($teamModel))
		{
			$team = $teamModel::forceCreate([
				'user_id' => $user->id,
				'name' => explode(' ', $user->name, 2)[0]."'s Team",
				'personal_team' => true,
			]);

			$user->current_team_id = $team->id;
			$user->save();
		}

		$this->info("✓ Admin user created successfully!");
		$this->newLine();
		$this->line("Email: {$email}");
		$this->line("Password: {$password}");
		$this->newLine();

		return self::SUCCESS;
	}
}
