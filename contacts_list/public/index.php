<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

use App\Controllers\RootController;
use App\Controllers\DashboardController;

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

// Create Twig
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

// Add RoutingMiddleware before we add the MethodOverrideMiddleware so the method is overridden before routing is done
$app->addRoutingMiddleware();

// Add MethodOverride middleware
$methodOverrideMiddleware = new MethodOverrideMiddleware();
$app->add($methodOverrideMiddleware);

$app->get('/', RootController::class . ":renderForm");
$app->group('/dashboard', function (RouteCollectorProxy $group) {
    $group->get('', DashboardController::class . ":renderView");
    $group->get('/create', DashboardController::class . ":renderCreate");
    $group->post('/create', DashboardController::class . ":processCreate");
    $group->get('/{id:[0-9]+}/update', DashboardController::class . ":renderUpdate");
    $group->put('/{id:[0-9]+}/update', DashboardController::class . ":processUpdate");
    $group->delete('/{id:[0-9]+}/delete', DashboardController::class . ":deleteAsync");
});

$app->run();
