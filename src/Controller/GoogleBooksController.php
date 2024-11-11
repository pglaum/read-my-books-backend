<?php

namespace App\Controller;

use App\Security\Voter\StandardVoter;
use App\Service\GoogleBooks\ApiClient;
use App\Service\GoogleBooks\Type\SearchQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/google-books', name: 'google_books_')]
class GoogleBooksController extends AbstractController
{
    #[Route('/', name: 'search')]
    #[IsGranted(StandardVoter::LOGGED_IN)]
    public function search(
        ApiClient $apiClient,
        #[MapQueryString] SearchQuery $query,
    ): Response {
        try {
            [$books, $total] = $apiClient->search($query);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        $savedBooks = [];

        return new Response(
            $this->serializer->serialize([
                'volume' => $books,
                'savedBooks' => $savedBooks,
                'total' => $total,
            ]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
        );
    }

    #[Route('/{volumeId}', name: 'get')]
    #[IsGranted(StandardVoter::LOGGED_IN)]
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

        $savedBook = null;

        return new Response(
            $this->serializer->serialize([
                'volume' => $result,
                'savedBook' => $savedBook,
            ]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
        );
    }
}
