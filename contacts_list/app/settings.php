<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'database' => [
                    'dbname' => 'db',
                    'username' => 'db',
                    'password' => 'db',
                    //'host' => '172.19.0.3', //will not work
                    'host' => 'db', //use the docker container name will auto resolve
                    'port' => '3306',
                ]
            ]);
        }
    ]);
};