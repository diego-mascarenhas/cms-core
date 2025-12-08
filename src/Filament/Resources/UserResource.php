<?php

namespace Idoneo\CmsCore\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Idoneo\CmsCore\Filament\Resources\UserResource\Pages;
use Laravel\Jetstream\Jetstream;

class UserResource extends Resource
{
	protected static ?string $model = User::class;

	public static function getNavigationIcon(): ?string
	{
		return 'heroicon-o-users';
	}

	public static function getNavigationLabel(): string
	{
		return __('Users');
	}

	public static function getModelLabel(): string
	{
		return __('User');
	}

	public static function getPluralModelLabel(): string
	{
		return __('Users');
	}

	public static function form(Schema $schema): Schema
	{
		return $schema
			->schema([
				Forms\Components\TextInput::make('name')
					->label(__('Name'))
					->required()
					->maxLength(255),

				Forms\Components\TextInput::make('password')
					->label(__('Password'))
					->password()
					->dehydrated(fn ($state) => filled($state))
					->required(fn (string $context): bool => $context === 'create')
					->maxLength(255),

				Forms\Components\TextInput::make('email')
					->label(__('Email'))
					->email()
					->required()
					->unique(ignoreRecord: true)
					->maxLength(255),

				Forms\Components\TextInput::make('phone')
					->label(__('Phone'))
					->numeric()
					->maxLength(20)
					->nullable(),

				Forms\Components\Select::make('role')
					->label(__('Role'))
					->options(function () {
						return collect(Jetstream::$roles)->mapWithKeys(function ($role) {
							return [$role->key => $role->name];
						})->toArray();
					})
				->default(function () {
					$roles = Jetstream::$roles;
					return !empty($roles) ? $roles[array_key_last($roles)]->key : 'guest';
				})
					->required()
					->helperText(__('Role is assigned to user personal team'))
					->columnSpanFull(),

				Forms\Components\KeyValue::make('data')
					->label(__('Additional Data'))
					->default([])
					->dehydrateStateUsing(fn ($state) => empty($state) ? null : $state)
					->nullable()
					->columnSpanFull(),
			])
			->columns(2);
	}

	public static function table(Table $table): Table
	{
		return $table
			->columns([
				Tables\Columns\TextColumn::make('name')
					->label(__('Name'))
					->searchable()
					->sortable(),

				Tables\Columns\TextColumn::make('email')
					->label(__('Email'))
					->searchable()
					->sortable(),

			Tables\Columns\TextColumn::make('phone')
				->label(__('Phone'))
				->searchable()
				->sortable(),

			Tables\Columns\TextColumn::make('role')
				->label(__('Role'))
			->badge()
			->color(function (string $state): string {
				$colors = [
					'admin' => 'success',
					'member' => 'info',
					'viewer' => 'warning',
				];
				return $colors[$state] ?? 'gray';
			})
			->formatStateUsing(function (string $state): string {
				$role = collect(Jetstream::$roles)->firstWhere('key', $state);
				return $role ? $role->name : ucfirst($state);
			})
			->getStateUsing(function ($record) {
				if ($record->currentTeam) {
					$membership = $record->currentTeam->users()
						->where('user_id', $record->id)
						->first();
				$defaultRole = !empty(Jetstream::$roles) ? Jetstream::$roles[array_key_last(Jetstream::$roles)]->key : 'viewer';
				return $membership?->membership->role ?? $defaultRole;
			}
			return !empty(Jetstream::$roles) ? Jetstream::$roles[array_key_last(Jetstream::$roles)]->key : 'viewer';
			}),

			Tables\Columns\TextColumn::make('email_verified_at')
				->label('Email verificado')
				->dateTime()
				->sortable()
				->toggleable(isToggledHiddenByDefault: true),

				Tables\Columns\TextColumn::make('created_at')
					->label('Creado')
					->dateTime()
					->sortable()
					->toggleable(isToggledHiddenByDefault: true),
		])
		->filters([
			//
		])
		->actions([
			//
		])
		->bulkActions([
			//
		]);
	}

	public static function getRelations(): array
	{
		return [
			//
		];
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ListUsers::route('/'),
			'create' => Pages\CreateUser::route('/create'),
			'edit' => Pages\EditUser::route('/{record}/edit'),
		];
	}
}
