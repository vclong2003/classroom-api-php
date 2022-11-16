<?php

namespace App\Controller;

use App\Entity\UserInfo;
use App\Repository\SessionRepository;
use App\Repository\UserInfoRepository;
use App\Repository\UserRepository;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/api/user', name: 'app_user_getAll', methods: ['GET'])]
    public function getAllUser(UserInfoRepository $userInfoRepo)
    {
        $data = $userInfoRepo->findAll();
        return new JsonResponse($data, 200, []);
    }
    #[Route('/api/user/role', name: 'app_user_getRow', methods: ['POST'])]
    public function test(Request $request, SessionRepository $sessionRepo, UserRepository $userRepo)
    {

        $userId = findUserId($request, $sessionRepo, $userRepo);
        $user = $userRepo->findOneBy(["id" => $userId]);

        return new JsonResponse(["role" => $user->getRole()], 202, []);
    }

    // public function findUserId($sessionId, SessionRepository $sessionRepo, UserRepository $userRepo): int
    // {
    //     $session = $sessionRepo->findOneBy(["sessionId" => $sessionId]);
    //     $userId = $session->getUserId();
    //     return $userId;
    // }
}
