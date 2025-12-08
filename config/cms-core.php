<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Teams Feature
	|--------------------------------------------------------------------------
	|
	| Enable or disable the teams functionality. When disabled, all team-related
	| UI elements will be hidden (team switcher, team settings, create team).
	| The underlying team structure remains for future activation.
	|
	*/

	'teams_enabled' => env('APP_TEAMS', false),

	/*
	|--------------------------------------------------------------------------
	| Team Model
	|--------------------------------------------------------------------------
	|
	| The model class used for teams. By default, this uses Laravel Jetstream's
	| Team model. You can customize this if you have a different Team model.
	|
	*/

	'team_model' => \App\Models\Team::class,

	/*
	|--------------------------------------------------------------------------
	| User Model
	|--------------------------------------------------------------------------
	|
	| The model class used for users. By default, this uses Laravel's User model.
	|
	*/

	'user_model' => \App\Models\User::class,

];
