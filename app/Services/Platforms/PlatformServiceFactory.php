<?php

namespace App\Services\Platforms;

use App\Models\Platform;

class PlatformServiceFactory
{
    public static function make(Platform $platform): PlatformServiceInterface
    {
        return match ($platform->type) {
            'twitter' => new TwitterService($platform),
            'instagram' => new InstagramService($platform),
            'linkedin' => new LinkedInService($platform),
            default => throw new \InvalidArgumentException("Unsupported platform type: {$platform->type}"),
        };
    }
}
