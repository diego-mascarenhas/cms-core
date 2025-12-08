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
		User::factory()->withPersonalTeam()->create([
			'name' => 'Admin',
			'email' => 'hola@humano.app',
			'password' => bcrypt('Simplicity!'),
			'email_verified_at' => now(),
		]);
	}
}
