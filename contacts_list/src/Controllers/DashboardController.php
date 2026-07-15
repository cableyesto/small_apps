<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController extends BaseController
{
    private $connection;
    public function __construct(PDO $connection) {
        $this->connection = $connection;
    }

    public function renderView(Request $request, Response $response, array $args): Response
    {
        return $this->render($request, $response, 'dashboard/index.html.twig', $args);
    }
}