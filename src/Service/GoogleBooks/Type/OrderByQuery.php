<?php

namespace App\Service\GoogleBooks\Type;

enum OrderByQuery: string
{
    case RELEVANCE = 'relevance';
    case NEWEST = 'newest';
}
