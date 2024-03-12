<?php

declare(strict_types=1);

namespace Bugover\Model;

use Bugover\Model\Contract\EntityContract;
use Bugover\Model\Entity\AuthenticateModel;
use Bugover\Model\Entity\ServiceModel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider as BaseProvider;

class ModelServiceProvider extends BaseProvider
{
    public function boot(): void
    {
        $this->app->bind(EntityContract::class, fn($app) => [
            App::make(ServiceModel::class),
            App::make(AuthenticateModel::class)
        ]);
    }
}
