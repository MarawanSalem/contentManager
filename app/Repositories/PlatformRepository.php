<?php

namespace App\Repositories;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Collection;

class PlatformRepository extends BaseRepository
{
    public function __construct(Platform $model)
    {
        parent::__construct($model);
    }

    public function getActivePlatforms(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function togglePlatformStatus(int $id): bool
    {
        $platform = $this->find($id);
        if ($platform) {
            $platform->is_active = !$platform->is_active;
            return $platform->save();
        }
        return false;
    }

    public function getPlatformsByType(string $type): Collection
    {
        return $this->model->where('type', $type)->get();
    }
}
