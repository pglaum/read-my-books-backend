<?php

namespace App\Service\GoogleBooks\Type;

use Symfony\Component\Validator\Constraints as Assert;

class SearchQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public string $query,

        #[Assert\NotBlank]
        public OrderByQuery $orderBy = OrderByQuery::RELEVANCE,

        #[Assert\GreaterThanOrEqual(0)]
        #[Assert\LessThanOrEqual(40)]
        public int $maxResults = 40,
    ) {
    }

    public function serialize(): string
    {
        return "{$this->query}-{$this->orderBy->value}-{$this->maxResults}";
    }
}
