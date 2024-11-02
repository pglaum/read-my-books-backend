<?php

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class Serializer implements SerializerInterface
{
    private const IGNORED_ATTRIBUTES = [
        '__intializer__', '__cloner__', '__isInitialized__',
    ];

    public const JSON_FORMAT = 'json';

    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function serialize(mixed $data, string $format = self::JSON_FORMAT, array $context = []): string
    {
        return $this->serializer->serialize(
            $data,
            $format,
            array_merge(
                [
                    AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                    AbstractNormalizer::IGNORED_ATTRIBUTES => self::IGNORED_ATTRIBUTES,
                    AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                        return ['id' => $object->getId()];
                    },
                    AbstractNormalizer::CALLBACKS => [
                        'start' => function ($elem) {
                            return $elem instanceof \DateTime ? $elem->format('Y-m-d') : null;
                        },
                        'end' => function ($elem) {
                            return $elem instanceof \DateTime ? $elem->format('Y-m-d') : null;
                        },
                    ],
                ],
                $context,
                [
                    AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => false,
                ],
            ),
        );
    }

    public function deserialize(mixed $data, string $type, string $format, array $context = []): mixed
    {
        return $this->serializer->deserialize($data, $type, $format, $context);
    }
}
