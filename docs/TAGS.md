# Sistema de Tags con Spatie Laravel Tags

Este paquete incluye soporte para tags usando `spatie/laravel-tags` y su integración con Filament.

## Instalación

Los paquetes ya están instalados:
- `spatie/laravel-tags`: ^4.0
- `filament/spatie-laravel-tags-plugin`: ^4.0

## Configuración

### 1. Publicar Migraciones

```bash
php artisan vendor:publish --provider="Spatie\Tags\TagsServiceProvider" --tag="tags-migrations"
php artisan migrate
```

### 2. Usar el Trait en tu Modelo

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

class Post extends Model
{
    use HasTags;

    protected $fillable = [
        'title',
        'content',
    ];
}
```

## Uso en Filament

### Formularios (SpatieTagsInput)

```php
use Filament\Forms\Components\SpatieTagsInput;

SpatieTagsInput::make('tags')
    ->label('Tags')
    ->placeholder('Agregar tags')
```

### Con Tipos (para Categorización)

```php
// Tags normales
SpatieTagsInput::make('tags')
    ->label('Tags')
    ->type('tags')

// Categorías
SpatieTagsInput::make('categories')
    ->label('Categorías')
    ->type('categories')
```

### Tablas (SpatieTagsColumn)

```php
use Filament\Tables\Columns\SpatieTagsColumn;

SpatieTagsColumn::make('tags')
    ->label('Tags')
```

### Con Tipos en Tablas

```php
SpatieTagsColumn::make('tags')
    ->label('Tags')
    ->type('tags')

SpatieTagsColumn::make('categories')
    ->label('Categorías')
    ->type('categories')
```

### Infolists (SpatieTagsEntry)

```php
use Filament\Infolists\Components\SpatieTagsEntry;

SpatieTagsEntry::make('tags')
    ->label('Tags')
```

## Ejemplo Completo: Resource con Tags

```php
<?php

namespace App\Filament\Resources;

use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Tables\Columns\SpatieTagsColumn;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('content')
                    ->required()
                    ->rows(10),

                // Tags normales
                SpatieTagsInput::make('tags')
                    ->label('Tags')
                    ->placeholder('Agregar tags'),

                // Categorías (usando tipos)
                SpatieTagsInput::make('categories')
                    ->label('Categorías')
                    ->type('categories')
                    ->placeholder('Agregar categorías'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                SpatieTagsColumn::make('tags')
                    ->label('Tags'),

                SpatieTagsColumn::make('categories')
                    ->label('Categorías')
                    ->type('categories'),
            ])
            ->filters([
                // Filtros por tags
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

## Uso Programático

### Agregar Tags

```php
$post = Post::create(['title' => 'Mi Post']);

// Agregar un tag
$post->attachTag('laravel');

// Agregar múltiples tags
$post->attachTags(['laravel', 'php', 'filament']);

// Agregar tag con tipo
$post->attachTag('tutorial', 'categories');
```

### Obtener Tags

```php
// Todos los tags
$post->tags;

// Tags por tipo
$post->tagsWithType('categories');
$post->tagsWithType('tags');
```

### Eliminar Tags

```php
$post->detachTag('laravel');
$post->detachTags(['laravel', 'php']);
```

## Categorización con Tipos

Los tipos permiten separar tags en diferentes categorías:

- **Tags**: Etiquetas generales (`type: null` o `type: 'tags'`)
- **Categories**: Categorías (`type: 'categories'`)
- **Custom**: Cualquier tipo personalizado

Ejemplo:

```php
// En el modelo
$post->attachTag('tutorial', 'categories');
$post->attachTag('laravel', 'tags');

// En Filament
SpatieTagsInput::make('tags')->type('tags');
SpatieTagsInput::make('categories')->type('categories');
```

## Ventajas

- ✅ Sistema robusto y probado de Spatie
- ✅ Integración nativa con Filament
- ✅ Soporte para tipos (categorización)
- ✅ Búsqueda y filtrado eficiente
- ✅ Traducción de tags (opcional)
- ✅ Ordenamiento de tags (opcional)
