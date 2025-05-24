<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Posts', Post::count())
                ->description('All posts in the system')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),
            Stat::make('Scheduled Posts', Post::where('status', 'scheduled')->count())
                ->description('Posts waiting to be published')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Published Posts', Post::where('status', 'published')->count())
                ->description('Successfully published posts')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
