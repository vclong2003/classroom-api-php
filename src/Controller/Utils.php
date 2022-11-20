<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

function getAuthInfo(Request $request, SessionRepository $sessionRepo, UserRepository $userRepo)
{
    try {
        $sessionId = $request->headers->get('sessionId');
        $session = $sessionRepo->findOneBy(["sessionId" => $sessionId]);
        if ($session == null) {
            return null;
        } else {
            $userId = $session->getUserId();

            $user = $userRepo->findOneBy(["id" => $userId]);
            $role = $user->getRole();

            $dataArray = ["userId" => $userId, "role" => strtolower($role)];
            return $dataArray;
        }
    } catch (\Exception $err) {
        return $err->getMessage();
    }
}