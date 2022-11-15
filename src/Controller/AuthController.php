<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\User;
use App\Entity\UserInfo;
use App\Repository\SessionRepository;
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

        return new JsonResponse(["msg" => "Registered!"], 201, []);
    }

    #[Route('/api/auth/login', name: 'app_auth_login', methods: ['POST'])]
    public function login(UserRepository $userRepo, Request $request, UserInfoRepository $userInfoRepo, SessionRepository $sessionRepo)
    {
        $data = json_decode($request->getContent(), true); //convert data to associative array
        $user = $userRepo->findOneBy(["email" => $data['email']]);
        $isPasswordTrue = password_verify($data['password'], $user->getPassword());

        if ($isPasswordTrue) {
            $session = new Session();
            $session->setUserId($user->getId());
            $session->setSessionId(bin2hex(random_bytes(20)));
            $session->setExpire(time() + 604800);
            $sessionRepo->save($session, true);

            return new JsonResponse(["msg" => "Logged in", "sessionId" => $session->getSessionId()], 200, []);
        } else {
            return new JsonResponse(["msg" => "Wrong password"], 400, []);
        }
    }

    #[Route('/api/auth/verifySessionId', name: 'app_auth_verify_sessionId', methods: ['POST'])]
    public function verifySessionId(Request $request, SessionRepository $sessionRepo, UserInfoRepository $userInfoRepo)
    {
        $data = $request->headers->get('sessionId');
        $sessionEntity = $sessionRepo->findOneBy(["sessionId" => $data]);

        if ($sessionEntity != null) {
            return new JsonResponse(["msg" => "Verified!"], 202, []);
        } else {
            return new JsonResponse(["msg" => "Verify failed!"], 406, []);
        }
    }
    // test github (long)
}
