<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserInfo;
use App\Repository\UserInfoRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    #[Route('/api/auth', name: 'app_auth_getUsers', methods: ['GET'])]
    public function getAllAccounts(UserRepository $userRepo)
    {
        $data = $userRepo->findAll();
        return new JsonResponse($data, 200, []);
    }

    #[Route('/api/auth/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(UserRepository $userRepo, Request $request, UserInfoRepository $userInfoRepo, ValidatorInterface $validator)
    {
        $data = json_decode($request->getContent(), true); //convert data to associative array

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT, []));
        $addedId = $userRepo->save($user, true);

        $userInfo = new UserInfo();
        $userInfo->setUserId($addedId);
        $userInfo->setName($data['name']);
        $userInfoRepo->save($userInfo, true);

        return new JsonResponse($userInfo, 200, []);
    }

    #[Route('/api/auth/login', name: 'app_auth_login', methods: ['POST'])]
    public function login(UserRepository $userRepo, Request $request, UserInfoRepository $userInfoRepo)
    {
        $data = json_decode($request->getContent(), true); //convert data to associative array
        $user = $userRepo->findOneBy(["email" => $data['email']]);
        $isPasswordTrue = password_verify($data['password'], $user->getPassword());

        if ($isPasswordTrue) {
            $userInfo = $userInfoRepo->findOneBy(["userId" => $user->getId()]);

            return new JsonResponse(["id" => $user->getId()], 200, []);
        } else {
            return new JsonResponse(["msg" => "wrong"], 200, []);
        }
    }
}
