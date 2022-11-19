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
    //REGISTER
    //takes: name, email, password
    #[Route('/api/auth/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(UserRepository $userRepo, Request $request, UserInfoRepository $userInfoRepo)
    {
        try {
            $data = json_decode($request->getContent(), true);    //convert data to associative array

            $user = new User();

            if ($data["name"] == "" || $data["email"] == "" || $data["password"] == "") {
                return new JsonResponse(["msg" => "Please enter full fields"], 400, []);
            } else if (strlen($data['password']) < 8) {
                return new JsonResponse(["msg" => "Password have at least 8 characters"], 400, []);
            } else if (!str_ends_with($data['email'], "@gmail.com")) {
                return new JsonResponse(["msg" => "Please enter a valid email address"], 400, []);
            }

            $user->setEmail($data['email']);
            $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT, []));
            $user->setRole('student');                 //default role: student

            $addedId = $userRepo->save($user, true);

            $userInfo = new UserInfo();
            $userInfo->setUserId($addedId);
            $userInfo->setName($data['name']);
            $userInfoRepo->save($userInfo, true);

            return new JsonResponse(["msg" => "Registered!"], 201, []);
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    //LOGIN
    //takes: email, password; return sessionId when logged in successfully
    #[Route('/api/auth/login', name: 'app_auth_login', methods: ['POST'])]
    public function login(UserRepository $userRepo, Request $request, SessionRepository $sessionRepo)
    {
        
        $data = json_decode($request->getContent(), true); //convert data to associative array
        
        if ($data["name"] == "" || $data["email"] == "" || $data["password"] == "") {
            return new JsonResponse(["Message" => "Please enter full fields"], 400, []);
        }
        
        $user = $userRepo->findOneBy(["email" => $data['email']]);
        $isPasswordTrue = password_verify($data['password'], $user->getPassword());

        if ($isPasswordTrue) {
            $session = new Session();
            $session->setUserId($user->getId());
            $session->setSessionId(bin2hex(random_bytes(20)));
            $session->setExpire(date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . '+ 7 days')));
            $sessionRepo->save($session, true);

            return new JsonResponse(["sessionId" => $session->getSessionId()], 200, []);
        } else {
            return new JsonResponse(["Message" => "Wrong password"], 400, []);
        }
    }

    //VERIFY SESSIONID
    // takes sessionId in headers, return 202 (exsist, not expire) if valid, else return 406
    #[Route('/api/auth', name: 'app_auth_verify_sessionId', methods: ['HEAD'])]
    public function verifySessionId(Request $request, SessionRepository $sessionRepo)
    {
        $data = $request->headers->get('sessionId');
        $sessionEntity = $sessionRepo->findOneBy(["sessionId" => $data]);

        if ($sessionEntity != null) {
            return new JsonResponse(["Message" => "Verified!"], 202, []);
        } else {
            return new JsonResponse(["Message" => "Verify failed!"], 406, []);
        }
    }
}
