<?php

namespace App\Controller;

use App\Entity\UserInfo;
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
    #[Route('/api/test', name: 'app_user_test', methods: ['POST'])]
    public function test(Request $request)
    {
        $data = $request->headers->get('sessionId');
        return new JsonResponse(["data" => $data, "time" => time()], 200, []);
        //test github
    }
}
