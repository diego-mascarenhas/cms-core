<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Idoneo\CmsCore\Traits\BelongsToCurrentTeam;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

class Post extends Model implements HasMedia
{
	use BelongsToCurrentTeam;
	use HasTags;
	use InteractsWithMedia;

	protected $fillable = [
		'user_id',
		'team_id',
		'title',
		'slug',
		'excerpt',
		'content',
		'status',
		'published_at',
	];

	protected $casts = [
		'published_at' => 'datetime',
	];

	/**
	 * Register media collections.
	 */
	public function registerMediaCollections(): void
	{
		$this->addMediaCollection('featured')
			->singleFile()
			->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

		$this->addMediaCollection('gallery')
			->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
	}

	/**
	 * Register media conversions.
	 */
	public function registerMediaConversions(?Media $media = null): void
	{
		// Thumbnail for listings (small, square, fast loading)
		$this->addMediaConversion('thumb')
			->width(150)
			->height(150)
			->fit(Fit::Crop, 150, 150)
			->sharpen(10)
			->quality(85)
			->nonQueued()
			->performOnCollections('featured', 'gallery');

		// Standard web size (optimized for web display)
		$this->addMediaConversion('web')
			->width(800)
			->height(600)
			->fit(Fit::Contain, 800, 600)
			->sharpen(10)
			->quality(90)
			->nonQueued()
			->performOnCollections('featured', 'gallery');
	}

	/**
	 * Get the user that owns the post.
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
