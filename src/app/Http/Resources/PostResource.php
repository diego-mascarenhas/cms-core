<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'slug' => $this->slug,
			'excerpt' => $this->excerpt,
			'content' => $this->content,
			'status' => $this->status,
			'published_at' => $this->published_at?->toIso8601String(),
			'created_at' => $this->created_at->toIso8601String(),
			'updated_at' => $this->updated_at->toIso8601String(),
			'author' => [
				'id' => $this->user->id,
				'name' => $this->user->name,
				'email' => $this->user->email,
			],
			'featured_image' => $this->getFeaturedImageUrl(),
			'gallery' => $this->getGalleryUrls(),
			'categories' => $this->getCategories(),
			'tags' => $this->getTags(),
		];
	}

	/**
	 * Get featured image URL.
	 */
	protected function getFeaturedImageUrl(): ?array
	{
		$media = $this->getFirstMedia('featured');

		if (!$media)
		{
			return null;
		}

		return [
			'url' => $media->getUrl(),
			'thumb' => $media->getUrl('thumb'),
			'web' => $media->getUrl('web'),
		];
	}

	/**
	 * Get gallery images URLs.
	 */
	protected function getGalleryUrls(): array
	{
		$gallery = $this->getMedia('gallery');

		return $gallery->map(function ($media) {
			return [
				'url' => $media->getUrl(),
				'thumb' => $media->getUrl('thumb'),
				'web' => $media->getUrl('web'),
			];
		})->toArray();
	}

	/**
	 * Get categories.
	 */
	protected function getCategories(): array
	{
		return $this->tagsWithType('categories')->map(function ($tag) {
			return [
				'id' => $tag->id,
				'name' => $tag->name,
				'slug' => $tag->slug,
			];
		})->toArray();
	}

	/**
	 * Get tags (excluding categories).
	 */
	protected function getTags(): array
	{
		// Get all tags that are not categories
		$tags = $this->tags()->where(function ($query) {
			$query->whereNull('type')
				->orWhere('type', '!=', 'categories');
		})->get();

		return $tags->map(function ($tag) {
			return [
				'id' => $tag->id,
				'name' => $tag->name,
				'slug' => $tag->slug,
			];
		})->toArray();
	}
}
