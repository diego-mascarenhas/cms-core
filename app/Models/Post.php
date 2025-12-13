<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Idoneo\CmsCore\Traits\BelongsToCurrentTeam;
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
		$this->addMediaConversion('thumb')
			->width(300)
			->height(300)
			->sharpen(10)
			->performOnCollections('featured', 'gallery');

		$this->addMediaConversion('large')
			->width(1200)
			->height(800)
			->sharpen(10)
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
