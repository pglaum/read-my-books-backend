<?php

namespace App\Controller;

use App\Service\GoogleBooks\ApiClient;
use App\Service\GoogleBooks\Type\SearchQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/google-books', name: 'google_books_')]
class GoogleBooksController extends AbstractController
{
    // TODO: security: allow only authenticated users
    #[Route('/', name: 'search')]
    public function search(
        ApiClient $apiClient,
        #[MapQueryString] SearchQuery $query,
    ): Response {
        try {
            $results = $apiClient->search($query);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return new Response(
            $this->serializer->serialize($results),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
        );
    }

    // TODO: security: allow only authenticated users
    #[Route('/{volumeId}', name: 'get')]
    public function get(string $volumeId, ApiClient $apiClient): Response
    {
        try {
            $result = $apiClient->get($volumeId);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return new Response(
            $this->serializer->serialize($result),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
        );
    }
}
