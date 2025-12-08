<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Teams Feature UI Visibility
	|--------------------------------------------------------------------------
	|
	| Controls the visibility of team-related UI elements. When set to false,
	| team switcher, create team, and team settings options are hidden.
	|
	| IMPORTANT: Users always have a personal team (Jetstream requirement).
	| This setting ONLY controls UI visibility, not the underlying functionality.
	|
	| When false: Single-tenant mode (personal team only, UI hidden)
	| When true: Multi-tenant mode (create/switch teams, full UI visible)
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
