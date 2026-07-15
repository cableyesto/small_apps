<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RootController extends BaseController
{
    private $connection;
    public function __construct(PDO $connection) {
        $this->connection = $connection;
    }

    public function renderForm(Request $request, Response $response, array $args): Response
    {
        $num_contacts = $this->connection->query('SELECT COUNT(*) FROM contacts')->fetchColumn();

        $arrayTemplate = ['contacts' => $num_contacts];
        return $this->render($request, $response, 'home/index.html.twig', $arrayTemplate);
    }
}