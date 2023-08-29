<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseApiController extends AbstractController
{
    protected function handleValidationErrors(array $errors): JsonResponse
    {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }
        return $this->failedMessage($errorMessages);
    }

    protected function notFoundMessage(string $message): JsonResponse
    {
        return new JsonResponse(['message' => $message], JsonResponse::HTTP_NOT_FOUND);
    }

    protected function createdMessage(string $message): JsonResponse
    {
        return new JsonResponse(['message' => $message], JsonResponse::HTTP_CREATED);
    }

    protected function successMessage(string $message): JsonResponse
    {
        return new JsonResponse(['message' => $message], JsonResponse::HTTP_OK);
    }
    protected function failedMessage(string $message): JsonResponse
    {
        return new JsonResponse(['message' => $message], JsonResponse::HTTP_BAD_REQUEST);
    }
}