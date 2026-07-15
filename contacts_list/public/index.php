<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use App\Controllers\RootController;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add Error Handling Middleware
$app->addErrorMiddleware(true, false, false);

$app->get('/', RootController::class . ":renderForm");

$app->run();

