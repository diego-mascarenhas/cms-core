<?php

namespace Idoneo\CmsCore\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class UsersChart extends ChartWidget
{
	protected static ?int $sort = 2;

	public function getHeading(): ?string
	{
		return 'Crecimiento de Usuarios';
	}

	protected function getData(): array
	{
		$data = $this->getUsersPerMonth();

		return [
			'datasets' => [
				[
					'label' => 'Usuarios registrados',
					'data' => $data['counts'],
					'borderColor' => 'rgb(59, 130, 246)',
					'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
					'fill' => true,
				],
			],
			'labels' => $data['labels'],
		];
	}

	protected function getType(): string
	{
		return 'line';
	}

	protected function getOptions(): array
	{
		return [
			'plugins' => [
				'legend' => [
					'display' => true,
				],
			],
			'scales' => [
				'y' => [
					'beginAtZero' => true,
					'ticks' => [
						'stepSize' => 1,
					],
				],
			],
		];
	}

	/**
	 * Get users count per month for the last 6 months.
	 */
	protected function getUsersPerMonth(): array
	{
		$userModel = config('auth.providers.users.model', 'App\\Models\\User');
		$labels = [];
		$counts = [];

		for ($i = 5; $i >= 0; $i--)
		{
			$date = now()->subMonths($i);
			$labels[] = $date->format('M Y');

			$count = $userModel::whereYear('created_at', $date->year)
				->whereMonth('created_at', $date->month)
				->count();

			$counts[] = $count;
		}

		return [
			'labels' => $labels,
			'counts' => $counts,
		];
	}
}
