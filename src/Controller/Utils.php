<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

function getAuthInfo(Request $request, SessionRepository $sessionRepo, UserRepository $userRepo): array
{

    try {
        $sessionId = $request->headers->get('sessionId');
        $session = $sessionRepo->findOneBy(["sessionId" => $sessionId]);
        $userId = $session->getUserId();

        $user = $userRepo->findOneBy(["id" => $userId]);
        $role = $user->getRole();

        $dataArray = ["userId" => $userId, "role" => strtolower($role)];
        return $dataArray;
    } catch (\Exception $err) {
        return new JsonResponse(["msg" => $err->getMessage()], 401, []);
    }
}
