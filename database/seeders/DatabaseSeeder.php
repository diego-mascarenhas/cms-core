<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 */
	public function run(): void
	{
		// Create default admin user with personal team (Jetstream)
		$admin = User::factory()->withPersonalTeam()->create([
			'name' => 'Admin',
			'email' => 'hola@humano.app',
			'password' => bcrypt('Simplicity!'),
			'email_verified_at' => now(),
		]);

		// Ensure team_user relationship exists with admin role
		if ($admin->currentTeam) {
			$admin->currentTeam->users()->syncWithoutDetaching([
				$admin->id => ['role' => 'admin']
			]);
		}
	}
}
