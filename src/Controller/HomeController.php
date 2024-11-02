<?php

namespace App\Controller;

use App\Service\GoogleBooks\ApiClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return new JsonResponse(['message' => 'Hello World!']);
    }

    #[Route('/test', name: 'test')]
    public function test(ApiClient $apiClient): Response
    {
        dd($apiClient->search('symfony'));

        return new JsonResponse(['message' => 'Test']);
    }
}
