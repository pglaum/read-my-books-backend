<?php

namespace App\Controller;

use App\Security\Voter\StandardVoter;
use Kreait\Firebase\Contract\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/auth', name: 'auth_')]
class AuthController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, Auth $auth): Response
    {
        $loginData = json_decode($request->getContent(), true) ?? [];

        if (!isset($loginData['email']) || !isset($loginData['password'])) {
            return $this->json(['message' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $auth->signInWithEmailAndPassword($loginData['email'], $loginData['password']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'accessToken' => $result->accessToken(),
            'data' => $result->data(),
            'firebaseTenantId' => $result->firebaseTenantId(),
            'firebaseUserId' => $result->firebaseUserId(),
            'idToken' => $result->idToken(),
            'refreshToken' => $result->refreshToken(),
            'ttl' => $result->ttl(),
        ]);
    }

    #[Route('/test', name: 'test', methods: ['GET'])]
    #[IsGranted(StandardVoter::LOGGED_IN)]
    public function test(): Response
    {
        return new JsonResponse(['message' => 'Hello from AuthController']);
    }
}
