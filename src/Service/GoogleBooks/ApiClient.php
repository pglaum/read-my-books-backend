<?php

namespace App\Service\GoogleBooks;

use App\Entity\GoogleVolume;
use App\Service\GoogleBooks\Type\SearchQuery;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiClient
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ParameterBagInterface $parameterBag,
        private readonly Security $security,
        private readonly CacheInterface $cache,
    ) {
    }

    public function search(
        SearchQuery $query,
    ): array {
        $key = 'google-books-search-'.$query->serialize();

        [$datetime, $response] = $this->cache->get($key, function (ItemInterface $item) use ($query): array {
            $item->expiresAfter(3600 * 24 * 31); // 1 month, then we remove it

            $response = $this->getClient()->request(
                'GET',
                '/books/v1/volumes',
                [
                    'query' => [
                        'q' => $query->query,
                        'maxResults' => $query->maxResults,
                        'orderBy' => $query->orderBy->value,
                        'startIndex' => $query->startIndex,
                    ],
                ],
            );

            return [new \DateTime(), $response->toArray()];
        });

        if ($datetime < new \DateTime('-1 week')) {
            $newResponse = $this->getClient()->request(
                'GET',
                '/books/v1/volumes',
                [
                    'headers' => [
                        'If-None-Match' => $response['etag'] ?? null,
                    ],
                    'query' => [
                        'q' => $query->query,
                        'maxResults' => $query->maxResults,
                        'orderBy' => $query->orderBy->value,
                        'startIndex' => $query->startIndex,
                    ],
                ],
            );

            if (304 !== $newResponse->getStatusCode()) {
                $response = $newResponse->toArray();
            }

            $this->cache->delete($key);
            $this->cache->get($key, function (ItemInterface $item) use ($response): array {
                $item->expiresAfter(3600 * 24 * 31); // 1 month, then we remove it

                return [new \DateTime(), $response];
            });
        }

        $data = $response;
        $total = $data['totalItems'] ?? 0;
        $items = $data['items'] ?? [];
        $results = [];
        foreach ($items as $item) {
            $results[] = new GoogleVolume($this->security->getUser()->getUserIdentifier(), $item);
        }

        return [$results, $total];
    }

    public function get(string $id): GoogleVolume
    {
        $key = 'google-books-get-'.$id;

        [$datetime, $response] = $this->cache->get($key, function (ItemInterface $item) use ($id): array {
            $item->expiresAfter(3600 * 24 * 31); // 1 month, then we remove it

            $response = $this->getClient()->request(
                'GET',
                '/books/v1/volumes/'.$id,
            );

            return [new \DateTime(), $response->toArray()];
        });

        if ($datetime < new \DateTime('-1 week')) {
            $newResponse = $this->getClient()->request(
                'GET',
                '/books/v1/volumes/'.$id,
                [
                    'headers' => [
                        'If-None-Match' => $response['etag'] ?? null,
                    ],
                ],
            );

            if (304 !== $newResponse->getStatusCode()) {
                $response = $newResponse->toArray();
            }

            $this->cache->delete($key);
            $this->cache->get($key, function (ItemInterface $item) use ($response): array {
                $item->expiresAfter(3600 * 24 * 31); // 1 month, then we remove it

                return [new \DateTime(), $response];
            });
        }

        return new GoogleVolume($this->security->getUser()->getUserIdentifier(), $response);
    }

    private function getClient(): HttpClientInterface
    {
        $apiKey = $this->parameterBag->get('firebaseApiKey');

        return $this->client->withOptions([
            'base_uri' => 'https://www.googleapis.com',
            'query' => [
                'key' => $apiKey,
            ],
        ]);
    }
}
