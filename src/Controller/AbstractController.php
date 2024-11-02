<?php

namespace App\Controller;

use App\Service\Serializer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractController extends BaseController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack $requestStack,
        protected EntityManagerInterface $em,
        protected Serializer $serializer,
    ) {
    }
}
