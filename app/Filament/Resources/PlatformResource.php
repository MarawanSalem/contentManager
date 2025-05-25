<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlatformResource\Pages;
use App\Filament\Resources\PlatformResource\RelationManagers;
use App\Models\Platform;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlatformResource extends Resource
{
    protected static ?string $model = Platform::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'twitter' => 'Twitter',
                        'instagram' => 'Instagram',
                        'linkedin' => 'LinkedIn',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        // Set platform-specific validation rules
                        switch ($state) {
                            case 'twitter':
                                $set('max_content_length', 280);
                                break;
                            case 'linkedin':
                                $set('max_content_length', 3000);
                                break;
                            case 'instagram':
                                $set('max_content_length', 2200);
                                break;
                        }
                    }),
                Forms\Components\Toggle::make('status')
                    ->label('Active')
                    ->default(true),
                Forms\Components\TextInput::make('max_content_length')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('api_key')
                    ->password()
                    ->required(),
                Forms\Components\TextInput::make('api_secret')
                    ->password()
                    ->required(),
                Forms\Components\TextInput::make('access_token')
                    ->password(),
                Forms\Components\TextInput::make('access_token_secret')
                    ->password(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'twitter' => 'sky',
                        'instagram' => 'pink',
                        'linkedin' => 'blue',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'twitter' => 'Twitter',
                        'instagram' => 'Instagram',
                        'linkedin' => 'LinkedIn',
                    ]),
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlatforms::route('/'),
            'create' => Pages\CreatePlatform::route('/create'),
            'edit' => Pages\EditPlatform::route('/{record}/edit'),
        ];
    }
}
