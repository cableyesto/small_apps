<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use PDOException;
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
        $arrayTemplate = ["contacts" => $contacts];
        return $this->render($request, $response, 'dashboard/index.html.twig', $arrayTemplate);
    }

    public function renderCreate(Request $request, Response $response, array $args): Response
    {
        return $this->render($request, $response, 'dashboard/create.html.twig', $args);
    }

    public function processCreate(Request $request, Response $response, array $args): Response
    {
        $params = (array)$request->getParsedBody();

        // Validation
        $params = (array)$request->getParsedBody();
        $validationForm = $this->validateForm($params);
        $error = $validationForm["error"];
        if (!is_null($error)) {
            $arrParamForView = ["error" => $error];
            return $this->render($request, $response, "dashboard/create.html.twig", $arrParamForView)->withStatus(400);
        }

        $validatedParams = $validationForm["validated"];

        try {
            $sql = "INSERT INTO contacts (nom, prenom, mail, telephone) VALUES (:lastname, :firstname, :email, :telephone);";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(":lastname", $validatedParams["lastname"], PDO::PARAM_STR);
            $stmt->bindParam(":firstname", $validatedParams["firstname"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $validatedParams["email"], PDO::PARAM_STR);
            $stmt->bindParam(":telephone", $validatedParams["telephone"], PDO::PARAM_STR);
            $stmt->execute();

            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        } catch (PDOException $e) {
            $arrParamForView = ["error" => $e->getMessage()];
            return $this->render($request, $response, "dashboard/create.html.twig", $arrParamForView)->withStatus(400);
        }

    }

    public function renderUpdate(Request $request, Response $response, array $args): Response
    {
        $sql = "SELECT * FROM contacts WHERE id = :id;";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":id", $args["id"], PDO::PARAM_INT);
        $stmt->execute();

        $contactWithId = $stmt->fetch();
        $arrayContactWithId = ["contact" => $contactWithId];

        return $this->render($request, $response, "dashboard/update.html.twig", $arrayContactWithId);
    }

    public function processUpdate(Request $request, Response $response, array $args): Response
    {
        // Retrieve the user in case the form with wrong data.
        $sql = "SELECT * FROM contacts WHERE id = :id;";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":id", $args["id"], PDO::PARAM_INT);
        $stmt->execute();

        $contactWithId = $stmt->fetch();

        // Validation
        $params = (array)$request->getParsedBody();
        $validationForm = $this->validateForm($params);
        $error = $validationForm["error"];
        if (!is_null($error)) {
            $arrParamForView = ["error" => $error, "contact" => $contactWithId];
            return $this->render($request, $response, "dashboard/update.html.twig", $arrParamForView)->withStatus(400);
        }

        $validatedParams = $validationForm["validated"];

        try {
            $sql = "UPDATE contacts SET nom=:lastname, prenom=:firstname, mail=:email, telephone=:telephone WHERE id=:id;";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(":lastname", $validatedParams["lastname"], PDO::PARAM_STR);
            $stmt->bindParam(":firstname", $validatedParams["firstname"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $validatedParams["email"], PDO::PARAM_STR);
            $stmt->bindParam(":telephone", $validatedParams["telephone"], PDO::PARAM_STR);
            $stmt->bindParam(":id", $args["id"], PDO::PARAM_INT);
            $stmt->execute();

            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        } catch (PDOException $e) {
            $arrParamForView = ["error" => $e->getMessage()];
            return $this->render($request, $response, "dashboard/create.html.twig", $arrParamForView)->withStatus(400);
        }
    }

    public function deleteAsync(Request $request, Response $response, array $args): Response
    {
        if ($request->getHeader('X-Requested-With')[0] !== 'XMLHttpRequest') {
            $response->getBody()->write(json_encode(["error" => "error"]));
            return $response;
        }

        $sql = "DELETE FROM contacts WHERE id = :id;";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":id", $args["id"], PDO::PARAM_INT);
        $stmt->execute();

        $response->getBody()->write(json_encode(["contact" => "deleted"]));
        return $response;
    }

    private function sanitizeInput($data): string
    {
        $data = trim($data);
        $data = stripslashes($data);
        return htmlspecialchars($data);
    }

    private function validateForm(array $params): array
    {
        $errorMessage = null;
        $resultParam = ["error" => $errorMessage, "validated" => []];
        if (!array_filter($params)) {
            $errorMessage = "Saisir tout les champs obligatoires";
            $resultParam["error"] = $errorMessage;
            return $resultParam;
        }

        $email = $this->sanitizeInput($params["mail"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = "Email saisi n'est pas valide";
            $resultParam["error"] = $errorMessage;
            return $resultParam;
        }

        $firstName = $this->sanitizeInput($params["prenom"]);
        $isFirstNameValid = preg_match('/^[a-zA-ZÀ-ÖØ-öø-ÿœŒ]+$/', $firstName);
        if ( $isFirstNameValid === 0) {
            $errorMessage = "Prénom saisi n'est pas valide";
            $resultParam["error"] = $errorMessage;
            return $resultParam;
        }

        $lastName = $this->sanitizeInput($params["nom"]);
        $isLastNameValid = preg_match('/^[a-zA-ZÀ-ÖØ-öø-ÿœŒ]+$/', $lastName);
        if ( $isLastNameValid === 0) {
            $errorMessage = "Nom saisi n'est pas valide";
            $resultParam["error"] = $errorMessage;
            return $resultParam;
        }

        $telephone = $this->sanitizeInput($params["telephone"]);
        $isTelephoneValid = preg_match('/^(?:0|\+33 ?)[1-9](?:[ .-]?\d{2}){4}$/', $telephone);
        if ( $isTelephoneValid === 0) {
            $errorMessage = "Numéro de téléphone saisi n'est pas valide";
            $resultParam = ["error" => $errorMessage];
            return $resultParam;
        }

        $resultParam["validated"] = [
            "email" => $email,
            "lastname" => $lastName,
            "firstname" => $firstName,
            "telephone" => $telephone
        ];
        return $resultParam;
    }
}