<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

function findUserId(Request $request, SessionRepository $sessionRepo): int
{
    try {
        $sessionId = $request->headers->get('sessionId');
        $session = $sessionRepo->findOneBy(["sessionId" => $sessionId]);
        $userId = $session->getUserId();
        return $userId; 
    } catch (\Exception $err) {
       return new JsonResponse (["msg" => $err->getMessage()], 401, []); 
    }
         
    
        
    
}