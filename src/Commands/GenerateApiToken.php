<?php

namespace Idoneo\CmsCore\Commands;

use Illuminate\Console\Command;

class GenerateApiToken extends Command
{
	protected $signature = 'cms-core:api-token
		{--email= : Email of the user to generate token for}
		{--name=API Token : Name for the token}';

	protected $description = 'Generate an API token for a user';

	public function handle(): int
	{
		$userModel = config('cms-core.user_model', \App\Models\User::class);
		$email = $this->option('email');

		if (!$email)
		{
			// Try to get admin user
			$user = $userModel::where('email', 'hola@humano.app')->first();

			if (!$user)
			{
				$this->error('No email provided and admin user not found.');
				$this->info('Usage: php artisan cms-core:api-token --email=user@example.com');
				return self::FAILURE;
			}
		}
		else
		{
			$user = $userModel::where('email', $email)->first();

			if (!$user)
			{
				$this->error("User with email '{$email}' not found.");
				return self::FAILURE;
			}
		}

		$tokenName = $this->option('name');
		$token = $user->createToken($tokenName, ['read']);

		$this->newLine();
		$this->info('API Token generated successfully!');
		$this->newLine();
		$this->line('Token Name: ' . $tokenName);
		$this->line('User: ' . $user->email);
		$this->newLine();
		$this->line('Add this to your .env file:');
		$this->line('APP_TOKEN=' . $token->plainTextToken);
		$this->newLine();
		$this->warn('⚠️  Save this token now! You won\'t be able to see it again.');
		$this->newLine();

		return self::SUCCESS;
	}
}
