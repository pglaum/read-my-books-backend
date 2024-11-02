<?php

namespace App\Service\GoogleBooks;

use App\Service\GoogleBooks\Type\SearchQuery;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiClient
{
    public function __construct(
        private HttpClientInterface $client,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function search(
        SearchQuery $query,
    ): array {
        // TODO: cache results
        // TODO: handle errors
        $response = $this->getClient()->request(
            'GET',
            '/books/v1/volumes',
            [
                'query' => [
                    'q' => $query->query,
                    'maxResults' => $query->maxResults,
                    'orderBy' => $query->orderBy->value,
                ],
            ],
        );

        return $response->toArray();
    }

    private function getClient(): HttpClientInterface
    {
        $apiKey = $this->parameterBag->get('firebaseApiKey');

        return $this->client->withOptions([
            'base_uri' => 'https://www.googleapis.com',
            'query' => [
                'apiKey' => $apiKey,
            ],
        ]);
    }

    public function get(string $id): array
    {
        // TODO: cache results
        // TODO: handle errors
        $response = $this->getClient()->request(
            'GET',
            '/books/v1/volumes/'.$id,
        );

        return $response->toArray();
    }
}
