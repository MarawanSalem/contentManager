<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Services\ActivityLogService;

class Analytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Analytics';
    protected static ?string $title = 'Post Analytics';
    protected static ?string $navigationGroup = 'Content Management';

    public function getViewData(): array
    {
        return [
            'stats' => app(ActivityLogService::class)->getActivityStats(),
        ];
    }

    public function getView(): string
    {
        return 'filament.pages.analytics';
    }
}
