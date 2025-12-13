<?php

namespace Idoneo\CmsCore\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Spatie\Tags\Tag;

class PostsChart extends ChartWidget
{
	protected static ?int $sort = 3;

	protected int | string | array $columnSpan = [
		'md' => 1,
		'xl' => 1,
	];

	public function getHeading(): ?string
	{
		return __('Posts by Category');
	}

	protected function getHeight(): ?string
	{
		return '250px';
	}

	protected function getData(): array
	{
		$data = $this->getPostsByCategory();

		return [
			'datasets' => [
				[
					'label' => __('Posts'),
					'data' => $data['counts'],
					'backgroundColor' => [
						'rgba(59, 130, 246, 0.8)',
						'rgba(16, 185, 129, 0.8)',
						'rgba(245, 158, 11, 0.8)',
						'rgba(239, 68, 68, 0.8)',
						'rgba(139, 92, 246, 0.8)',
						'rgba(236, 72, 153, 0.8)',
					],
					'borderColor' => [
						'rgb(59, 130, 246)',
						'rgb(16, 185, 129)',
						'rgb(245, 158, 11)',
						'rgb(239, 68, 68)',
						'rgb(139, 92, 246)',
						'rgb(236, 72, 153)',
					],
					'borderWidth' => 1,
				],
			],
			'labels' => $data['labels'],
		];
	}

	protected function getType(): string
	{
		return 'doughnut';
	}

	protected function getOptions(): array
	{
		return [
			'plugins' => [
				'legend' => [
					'display' => true,
					'position' => 'bottom',
				],
			],
			'maintainAspectRatio' => false,
		];
	}



	/**
	 * Get posts count by category.
	 */
	protected function getPostsByCategory(): array
	{
		$teamId = auth()->check() && auth()->user()->currentTeam ? auth()->user()->currentTeam->id : null;

		$categories = Tag::where('type', 'categories')
			->get()
			->map(function ($category) use ($teamId) {
				$query = Post::whereHas('tags', function ($q) use ($category) {
					$q->where('tags.id', $category->id)
						->where('tags.type', 'categories');
				});

				if ($teamId)
				{
					$query->where('team_id', $teamId);
				}

				return [
					'id' => $category->id,
					'name' => $category->name,
					'count' => $query->count(),
				];
			})
			->sortByDesc('count')
			->take(6)
			->values();

		$labels = [];
		$counts = [];
		$categoryIds = [];

		foreach ($categories as $category)
		{
			$labels[] = $category['name'];
			$counts[] = $category['count'];
			$categoryIds[] = $category['id'];
		}

		return [
			'labels' => $labels,
			'counts' => $counts,
			'categoryIds' => $categoryIds,
		];
	}
}
