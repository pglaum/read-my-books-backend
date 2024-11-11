<?php

namespace App\Service\GoogleBooks;

use App\Entity\GoogleVolume;
use App\Service\GoogleBooks\Type\SearchQuery;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiClient
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ParameterBagInterface $parameterBag,
        private readonly Security $security,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
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

        $data = $response->toArray();
        $total = $data['totalItems'] ?? 0;
        $items = $data['items'] ?? [];
        $results = [];
        foreach ($items as $item) {
            $results[] = new GoogleVolume($this->security->getUser()->getUserIdentifier(), $item);
        }

        return [$results, $total];
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

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function get(string $id): GoogleVolume
    {
        // TODO: cache results
        // TODO: handle errors
        $response = $this->getClient()->request(
            'GET',
            '/books/v1/volumes/'.$id,
        );

        return new GoogleVolume($this->security->getUser()->getUserIdentifier(), $response->toArray());
    }
}
