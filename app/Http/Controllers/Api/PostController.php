<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PostIndexRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
	/**
	 * Display a listing of posts with filters.
	 */
	public function index(PostIndexRequest $request): AnonymousResourceCollection
	{
		$query = Post::query();

		// Apply team scope if teams are enabled
		if (config('cms.teams_enabled', true) && auth()->user()->currentTeam)
		{
			$query->where('team_id', auth()->user()->currentTeam->id);
		}

		// Filter by status
		if ($request->filled('status'))
		{
			$query->where('status', $request->status);
		}
		else
		{
			// Default: only show published posts
			$query->where('status', 'published');
		}

		// Filter by category
		if ($request->filled('category'))
		{
			$query->whereHas('tags', function ($q) use ($request) {
				$q->where('tags.name', $request->category)
					->where('tags.type', 'categories');
			});
		}

		// Filter by tag
		if ($request->filled('tag'))
		{
			$query->whereHas('tags', function ($q) use ($request) {
				$q->where('tags.name', $request->tag);
			});
		}

		// Search in title, excerpt, and content
		if ($request->filled('search'))
		{
			$search = $request->search;
			$query->where(function ($q) use ($search) {
				$q->where('title', 'like', "%{$search}%")
					->orWhere('excerpt', 'like', "%{$search}%")
					->orWhere('content', 'like', "%{$search}%");
			});
		}

		// Order by published_at (most recent first)
		$query->orderBy('published_at', 'desc');

		// Paginate results
		$perPage = $request->get('per_page', 15);
		$posts = $query->paginate($perPage);

		return PostResource::collection($posts);
	}

	/**
	 * Display the specified post.
	 */
	public function show(string $slug): JsonResponse|PostResource
	{
		$query = Post::where('slug', $slug);

		// Apply team scope if teams are enabled
		if (config('cms.teams_enabled', true) && auth()->user()->currentTeam)
		{
			$query->where('team_id', auth()->user()->currentTeam->id);
		}

		$post = $query->firstOrFail();

		// Only show published posts unless user has permission
		if ($post->status !== 'published' && !auth()->user()->can('viewAny', Post::class))
		{
			abort(404);
		}

		return new PostResource($post);
	}
}
