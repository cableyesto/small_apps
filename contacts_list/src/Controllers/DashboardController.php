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
        $contacts = $this->connection->query('SELECT * FROM contacts;')->fetchAll();
        $arrayTemplate = ['contacts' => $contacts];
        return $this->render($request, $response, 'dashboard/index.html.twig', $arrayTemplate);
    }

    public function renderCreate(Request $request, Response $response, array $args): Response
    {
        return $this->render($request, $response, 'dashboard/create.html.twig', $args);
    }

    public function processCreate(Request $request, Response $response, array $args): Response
    {
        $params = (array)$request->getParsedBody();
        if (!array_filter($params)) {
            $errorMessage = "Saisir tout les champs obligatoires";
            return $this->render($request, $response, 'dashboard/create.html.twig', ["error" => $errorMessage]);
        }

        $email = $this->sanitizeInput($params["mail"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorEmail = "Email saisi n'est pas valide";
            return $this->render($request, $response, 'dashboard/create.html.twig', ["error" => $errorEmail]);
        }

        $firstName = $this->sanitizeInput($params["prenom"]);
        $isFirstNameValid = preg_match('/^[a-zA-ZÀ-ÖØ-öø-ÿœŒ]+$/', $firstName);
        if ( $isFirstNameValid === 0) {
            $errorFirstName = "Prénom saisi n'est pas valide";
            return $this->render($request, $response, 'dashboard/create.html.twig', ["error" => $errorFirstName]);
        }

        $lastName = $this->sanitizeInput($params["nom"]);
        $isLastNameValid = preg_match('/^[a-zA-ZÀ-ÖØ-öø-ÿœŒ]+$/', $lastName);
        if ( $isLastNameValid === 0) {
            $errorLastName = "Nom saisi n'est pas valide";
            return $this->render($request, $response, 'dashboard/create.html.twig', ["error" => $errorLastName]);
        }

        $telephone = $this->sanitizeInput($params["telephone"]);
        $isTelephoneValid = preg_match('/((\+)33|0|0033)[1-9](\d{2}){4}/', $telephone);
        if ( $isTelephoneValid === 0) {
            $errorTelephone = "Numéro de téléphone saisi n'est pas valide";
            return $this->render($request, $response, 'dashboard/create.html.twig', ["error" => $errorTelephone]);
        }

        $sql = "INSERT INTO contacts (nom, prenom, mail, telephone) VALUES (:lastname, :firstname, :email, :telephone);";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":lastname", $lastName, PDO::PARAM_STR);
        $stmt->bindParam(":firstname", $firstName, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":telephone", $telephone, PDO::PARAM_STR);
        $stmt->execute();

        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    private function sanitizeInput($data): string
    {
        $data = trim($data);
        $data = stripslashes($data);
        return htmlspecialchars($data);
    }
}