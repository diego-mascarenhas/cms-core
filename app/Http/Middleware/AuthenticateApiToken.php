<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle(Request $request, Closure $next): Response
	{
		// If user is already authenticated via Sanctum, continue
		if (auth()->check())
		{
			return $next($request);
		}

		$appToken = env('APP_TOKEN');
		$bearerToken = $request->bearerToken();

		// If APP_TOKEN is set in .env and matches the Bearer token
		if ($appToken && $bearerToken && hash_equals($appToken, $bearerToken))
		{
			// Try to find the token in database
			$token = \Laravel\Sanctum\PersonalAccessToken::findToken($bearerToken);

			if ($token && $token->tokenable)
			{
				auth()->login($token->tokenable);
				return $next($request);
			}

			// If token not found in DB but matches APP_TOKEN, authenticate admin user
			$adminUser = User::where('email', env('APP_TOKEN_USER', 'hola@humano.app'))->first();

			if ($adminUser)
			{
				auth()->login($adminUser);
				return $next($request);
			}
		}

		// Fall back to Sanctum's default authentication
		return $next($request);
	}
}
