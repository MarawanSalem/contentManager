<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestActivity extends BaseWidget
{
    protected static ?string $heading = 'Latest Activity';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(ActivityLog::latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User'),
                Tables\Columns\TextColumn::make('action')
                    ->label('Action'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
