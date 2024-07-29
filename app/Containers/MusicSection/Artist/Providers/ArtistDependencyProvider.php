<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Providers;

use App\Containers\MusicSection\Track\Facades\TrackFacadeInterface;
use App\Ship\Parents\Providers\DependencyProvider;

final class ArtistDependencyProvider extends DependencyProvider
{
    public function getTrackFacade(): TrackFacadeInterface
    {
        return app(TrackFacade::class);
    }
}
