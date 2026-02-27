<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Actions\EventAction;
use App\Application\Actions\StatisticsAction;

return function (App $app) {
    $app->post('/event', EventAction::class);
    $app->get('/statistics', StatisticsAction::class);
};
