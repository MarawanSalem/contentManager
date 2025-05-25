<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use App\Models\Platform;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Filament\Actions;
use Filament\Actions\Exports\Models\Export;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->default('Untitled Post'),
                Forms\Components\Textarea::make('content')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->default('')
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $platforms = Platform::whereIn('id', $get('platforms'))->get();
                        foreach ($platforms as $platform) {
                            switch ($platform->type) {
                                case 'twitter':
                                    $set('content', Str::limit($state, 280));
                                    break;
                                case 'linkedin':
                                    $set('content', Str::limit($state, 3000));
                                    break;
                            }
                        }
                    }),
                Forms\Components\DateTimePicker::make('scheduled_time')
                    ->required()
                    ->default(now()),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('platforms')
                    ->relationship('platforms', 'name')
                    ->multiple()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $platforms = Platform::whereIn('id', $state)->get();
                        foreach ($platforms as $platform) {
                            switch ($platform->type) {
                                case 'twitter':
                                    $set('content', Str::limit($get('content'), 280));
                                    break;
                                case 'linkedin':
                                    $set('content', Str::limit($get('content'), 3000));
                                    break;
                            }
                        }
                    }),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('posts')
                    ->visibility('public')
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('16:9')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1080')
                    ->required(fn (Forms\Get $get) =>
                        collect($get('platforms'))->contains(fn ($id) =>
                            Platform::find($id)?->type === 'instagram'
                        )
                    ),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                        'published' => 'Published',
                        'failed' => 'Failed',
                    ])
                    ->required()
                    ->default('draft'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        'published' => 'success',
                        'failed' => 'danger',
                    }),
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                        'published' => 'Published',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\Filter::make('scheduled_time')
                    ->form([
                        Forms\Components\DatePicker::make('scheduled_from'),
                        Forms\Components\DatePicker::make('scheduled_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['scheduled_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_time', '>=', $date),
                            )
                            ->when(
                                $data['scheduled_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_time', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->action(function (Post $record) {
                        $record->update(['status' => 'published']);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Post $record) => $record->status === 'scheduled'),
                Tables\Actions\Action::make('schedule')
                    ->action(function (Post $record) {
                        $record->update(['status' => 'scheduled']);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Post $record) => $record->status === 'draft'),
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'content', 'user.name'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user']);
    }

    public static function getActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(Export::class)
                ->formats([
                    'csv',
                    'xlsx',
                ]),
        ];
    }
}
