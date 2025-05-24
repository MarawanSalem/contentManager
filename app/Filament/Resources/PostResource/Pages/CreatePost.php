<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure all required fields are present
        if (!isset($data['title'])) {
            $data['title'] = 'Untitled Post';
        }

        if (!isset($data['content'])) {
            $data['content'] = '';
        }

        if (!isset($data['status'])) {
            $data['status'] = 'draft';
        }

        return $data;
    }
}
