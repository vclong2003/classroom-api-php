<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

function findUserId(Request $request, SessionRepository $sessionRepo): int
{
    $sessionId = $request->headers->get('sessionId');
    $session = $sessionRepo->findOneBy(["sessionId" => $sessionId]);
    $userId = $session->getUserId();
    return $userId;
}
