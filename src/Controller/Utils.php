<?php

use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

function getAuthInfo(Request $request, SessionRepository $sessionRepo, UserRepository $userRepo)
{

    $sessionId = $request->headers->get('sessionId');
    $session = $sessionRepo->findOneBy(["sessionId" => $sessionId]);
    if ($session == null) {
        return null;
    }

    $currentTime = time();
    if ($currentTime > strtotime($session->getExpire())) {
        return null;
    }

    $userId = $session->getUserId();
    $user = $userRepo->findOneBy(["id" => $userId]);
    if ($user == null) {
        return null;
    }

    return $user;
}
