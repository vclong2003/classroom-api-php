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

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    #[Route('/', name: 'app_auth_getUsers', methods: ['GET'])]
    public function getAllAccounts(UserRepository $userRepo)
    {
        $data = $userRepo->findAll();
        return new JsonResponse($data, 200, []);
    }

    #[Route('/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(UserRepository $userRepo, Request $request, UserInfoRepository $userInfoRepo, ValidatorInterface $validator)
    {
        try {
            $data = json_decode($request->getContent(), true); //convert data to associative array
            try {

                if ($data['name'] == "") {
                    return new JsonResponse(["Message" => "Please enter all field"], 404, []);
                }

                if ($data['email'] == "") {
                    return new JsonResponse(["Message" => "Please enter all field"], 404, []);
                } else if ($data['email'] . str_ends_with("@gmail.com", true)) {
                    return new JsonResponse(["Message" => "Invalid email"], 404, []);
                }

                if (strlen($data['password']) < 8) {
                    return new JsonResponse(["Message" => "Password must include at least 8 characters"], 404, []);
                }

                $user = new User();
                $user->setEmail($data['email']);
                $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT, []));
            } catch (\Exception $err) {
                return new JsonResponse(["Message" => "$err"], 404, []);
            }


            $addedId = $userRepo->save($user, true);

            $userInfo = new UserInfo();
            $userInfo->setUserId($addedId);
            $userInfo->setName($data['name']);
            $userInfoRepo->save($userInfo, true);

            return new JsonResponse(["Message" => "Registered!"], 201, []);
        } catch (\Exception $err) {
            return new JsonResponse(["Message" => "$err"], 404, []);
        }
    }

    #[Route('/login', name: 'app_auth_login', methods: ['POST'])]
    public function login(UserRepository $userRepo, Request $request, UserInfoRepository $userInfoRepo, SessionRepository $sessionRepo)
    {
        try {
            try {
                $data = json_decode($request->getContent(), true); //convert data to associative array
                if ($data['email'] == "") {
                    return new JsonResponse(["Message" => "Email or password are incorrect"], 404, []);
                } else if ($data['password'] == "") {
                    return new JsonResponse(["Message" => "Email or password are incorrect"], 404, []);
                }

                $user = $userRepo->findOneBy(["email" => $data['email']]);
                $isPasswordTrue = password_verify($data['password'], $user->getPassword());
            } catch (\Exception $err) {
                return new JsonResponse(["Message" => "$err"], 404, []);
            }


            if ($isPasswordTrue) {
                $session = new Session();
                $session->setUserId($user->getId());
                $session->setSessionId(bin2hex(random_bytes(20)));
                $session->setExpire(time() + 604800);
                $sessionRepo->save($session, true);

                return new JsonResponse(["userInfo" => $userInfoRepo->findOneBy(["userId" => $user->getId()]), "sessionId" => $session->getSessionId()], 200, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["Message" => "Email or password are incorrect"], 404, []);
        }
    }

    #[Route('/api/auth/verifySessionId', name: 'app_auth_verify_sessionId', methods: ['POST'])]
    public function verifySessionId(Request $request, SessionRepository $sessionRepo, UserInfoRepository $userInfoRepo)
    {
        $data = $request->headers->get('sessionId');
        $sessionEntity = $sessionRepo->findOneBy(["sessionId" => $data]);

        if ($sessionEntity != null) {
            return new JsonResponse(["userInfo" => $userInfoRepo->findOneBy(["userId" => $sessionEntity->getUserId()])], 202, []);
        } else {
            return new JsonResponse(["msg" => "Verify failed!"], 406, []);
        }
    }
}

// test ti xem sao ? 
