<?php

namespace Idoneo\CmsCore\Filament\Resources;

use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Table;
use Idoneo\CmsCore\Filament\Resources\PostResource\Pages;
use Illuminate\Support\Str;
use Spatie\Tags\Tag;

class PostResource extends Resource
{
	protected static ?string $model = Post::class;

	public static function getNavigationIcon(): ?string
	{
		return 'heroicon-o-document-text';
	}

	public static function getNavigationLabel(): string
	{
		return __('Posts');
	}

	public static function getModelLabel(): string
	{
		return __('Post');
	}

	public static function getPluralModelLabel(): string
	{
		return __('Posts');
	}

	public static function form(Schema $schema): Schema
	{
		return $schema
			->schema([
				Section::make(__('Content'))
					->schema([
						Forms\Components\TextInput::make('title')
							->label(__('Title'))
							->required()
							->maxLength(255)
							->live(onBlur: true)
							->afterStateUpdated(function (Set $set, ?string $state) {
								if (filled($state))
								{
									$set('slug', Str::slug($state));
								}
							}),

						Forms\Components\TextInput::make('slug')
							->label(__('Slug'))
							->required()
							->unique(ignoreRecord: true)
							->maxLength(255)
							->alphaDash(),

						Forms\Components\Textarea::make('excerpt')
							->label(__('Excerpt'))
							->rows(3)
							->maxLength(500)
							->columnSpanFull(),

						Forms\Components\RichEditor::make('content')
							->label(__('Content'))
							->toolbarButtons([
								'bold',
								'italic',
								'underline',
								'link',
								'bulletList',
								'orderedList',
							])
							->columnSpanFull(),
					])
					->columns(2),

				Section::make(__('Media'))
					->schema([
						SpatieMediaLibraryFileUpload::make('featured')
							->label(__('Featured Image'))
							->collection('featured')
							->image()
							->imageEditor()
							->conversion('thumb')
							->columnSpanFull(),

						SpatieMediaLibraryFileUpload::make('gallery')
							->label(__('Gallery'))
							->collection('gallery')
							->multiple()
							->image()
							->imageEditor()
							->reorderable()
							->conversion('thumb')
							->columnSpanFull(),
					]),

				Section::make(__('Tags & Categories'))
					->schema([
						SpatieTagsInput::make('tags')
							->label(__('Tags'))
							->type('tags')
							->placeholder(__('Add tags')),

						SpatieTagsInput::make('categories')
							->label(__('Categories'))
							->type('categories')
							->placeholder(__('Add categories')),
					])
					->columns(2),

				Section::make(__('Settings'))
					->schema([
						Forms\Components\Select::make('status')
							->label(__('Status'))
							->options([
								'draft' => __('Draft'),
								'published' => __('Published'),
								'archived' => __('Archived'),
							])
							->default('draft')
							->required(),

						Forms\Components\DateTimePicker::make('published_at')
							->label(__('Published At'))
							->visible(fn (Get $get) => $get('status') === 'published')
							->default(now()),
					])
					->columns(2),
			]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->columns([
				SpatieMediaLibraryImageColumn::make('featured')
					->label(__('Featured'))
					->collection('featured')
					->size(50),

				Tables\Columns\TextColumn::make('title')
					->label(__('Title'))
					->searchable()
					->sortable()
					->limit(50),

				Tables\Columns\TextColumn::make('user.name')
					->label(__('Author'))
					->searchable()
					->sortable(),

				SpatieTagsColumn::make('tags')
					->label(__('Tags'))
					->type('tags'),

				SpatieTagsColumn::make('categories')
					->label(__('Categories'))
					->type('categories'),

				Tables\Columns\SelectColumn::make('status')
					->label(__('Status'))
					->options([
						'draft' => __('Draft'),
						'published' => __('Published'),
						'archived' => __('Archived'),
					])
					->selectablePlaceholder(false),

				Tables\Columns\TextColumn::make('published_at')
					->label(__('Published At'))
					->dateTime()
					->sortable()
					->toggleable(isToggledHiddenByDefault: true),

				Tables\Columns\TextColumn::make('created_at')
					->label(__('Created At'))
					->dateTime()
					->sortable()
					->toggleable(isToggledHiddenByDefault: true),
			])
			->filters([
				Tables\Filters\SelectFilter::make('status')
					->label(__('Status'))
					->options([
						'draft' => __('Draft'),
						'published' => __('Published'),
						'archived' => __('Archived'),
					]),

				Tables\Filters\Filter::make('published_at')
					->label(__('Published'))
					->query(fn ($query) => $query->whereNotNull('published_at')),

				Tables\Filters\SelectFilter::make('tags')
					->label(__('Tags'))
					->multiple()
					->options(function () {
						return Tag::where('type', 'tags')
							->get()
							->mapWithKeys(function ($tag) {
								return [$tag->id => $tag->name];
							})
							->toArray();
					})
					->query(function ($query, array $data) {
						if (!empty($data['values']))
						{
							$query->whereHas('tags', function ($q) use ($data) {
								$q->whereIn('tags.id', $data['values'])
									->where('tags.type', 'tags');
							});
						}
					})
					->searchable(),

				Tables\Filters\SelectFilter::make('categories')
					->label(__('Categories'))
					->multiple()
					->options(function () {
						return Tag::where('type', 'categories')
							->get()
							->mapWithKeys(function ($tag) {
								return [$tag->id => $tag->name];
							})
							->toArray();
					})
					->query(function ($query, array $data) {
						if (!empty($data['values']))
						{
							$query->whereHas('tags', function ($q) use ($data) {
								$q->whereIn('tags.id', $data['values'])
									->where('tags.type', 'categories');
							});
						}
					})
					->searchable(),
			])
			->recordActions([
				EditAction::make(),
			])
			->toolbarActions([
				BulkActionGroup::make([
					DeleteBulkAction::make(),
				]),
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
			'index' => Pages\ListPosts::route('/'),
			'create' => Pages\CreatePost::route('/create'),
			'edit' => Pages\EditPost::route('/{record}/edit'),
		];
	}
}
