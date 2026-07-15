<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $dbSettings = $settings->get('database');
            $db = [
              'dbname' => $dbSettings['dbname'],
              'host'   => $dbSettings['host'],
              'port'   => $dbSettings['port'],
              'user'   => $dbSettings['username'],
              'pass'   => $dbSettings['password'],
            ];
            try {
                $dsn = "mysql:host=" . $db['host'] . ";port=" . $db['port'] . ";dbname=" . $db['dbname'] . ';charset=utf8mb4';
                $connection = new PDO( $dsn, $db['user'], $db['pass'] );
                $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                return $connection;
            } catch (PDOException $exception) {
                // If there is an error with the connection, stop the script and display the error.
                exit('Failed to connect to database!');
            }
        }
    ]);
};