<?php

declare(strict_types=1);

namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

abstract class BaseController
{
    protected function render(Request $request, Response $response, string $template, array $args): Response
    {
        $renderer = Twig::fromRequest($request);
        $renderer->render($response, $template, $args);
        return $response;
    }
}