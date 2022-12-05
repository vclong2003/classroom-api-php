<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Session;
use App\Repository\AdminRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    //LOGIN
    //body params: email, password
    #[Route('/admin/api/auth', name: 'app_admin_login',  methods: ['POST'])]
    public function login(Request $request, UserRepository $userRepo, SessionRepository $sessionRepo, AdminRepository $adminRepo): Response
    {
        try {
            $data = json_decode($request->getContent(), true); //convert data to associative array

            if ($data["email"] == "" || $data["password"] == "") {
                return new JsonResponse(["msg" => "Please enter full fields"], 400, []);
            }

            $user = $userRepo->findOneBy(["email" => $data['email']]);
            if ($user == null) {
                return new JsonResponse(["msg" => "account not found"], 404, []);
            }

            $admin = $adminRepo->findOneBy(['userId' => $user->getId()]);
            if ($admin == null) {
                return new JsonResponse(["msg" => "account not allowed"], 403, []);
            }

            $isPasswordTrue = password_verify($data['password'], $user->getPassword());
            if ($isPasswordTrue) {
                $session = new Session();
                $session->setUserId($user->getId());
                $session->setSessionId(bin2hex(random_bytes(20)));
                $session->setExpire(date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . '+ 7 days')));
                $sessionRepo->save($session, true);

                return new JsonResponse(["sessionId" => $session->getSessionId()], 200, []);
            } else {
                return new JsonResponse(["msg" => "Wrong password"], 403, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    #[Route('/admin/api/user', name: 'app_admin_get_users',  methods: ['GET'])]
    public function getAllUser(Request $request, UserRepository $userRepo,  ManagerRegistry $managerReg): Response
    {
        try {

            if (!$this->verifySessionId($managerReg, $request)) {
                return new JsonResponse(['msg' => 'unauthorized'], 403, []);
            }

            $users = $userRepo->findAll();

            return new JsonResponse($users, 200, []);
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    //SET ROLE
    //param: userId
    //body params: role
    #[Route('/admin/api/user/{userId}/role', name: 'app_admin_set_user_role',  methods: ['POST'])]
    public function setUserRole($userId, Request $request, UserRepository $userRepo,  ManagerRegistry $managerReg): Response
    {
        try {
            if (!$this->verifySessionId($managerReg, $request)) {
                return new JsonResponse(['msg' => 'unauthorized'], 403, []);
            }

            $predefinedRole = ['student', 'teacher'];
            $data = json_decode($request->getContent(), true); //convert data to associative array

            $user = $userRepo->findOneBy(['id' => $userId]);
            if ($user == null) {
                return new JsonResponse(['msg' => 'user not found'], 404, []);
            }

            if (!in_array($data['role'], $predefinedRole)) {
                return new JsonResponse(['msg' => 'role not valid'], 406, []);
            }
            $user->setRole($data['role']);
            $userRepo->save($user, true);

            return new JsonResponse(['msg' => 'ok'], 200, []);
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    function verifySessionId(ManagerRegistry $managerReg, Request $request)
    {
        try {
            $sessionId = $request->headers->get('sessionId');

            $entityManager = $managerReg->getManager();
            $sessionRepo = $entityManager->getRepository(Session::class);
            $adminRepo = $entityManager->getRepository(Admin::class);

            $session = $sessionRepo->findOneBy(['sessionId' => $sessionId]);
            if ($session == null) {
                return false;
            }

            $admin = $adminRepo->findOneBy(['userId' => $session->getUserId()]);
            if ($admin == null) {
                return false;
            }

            return true;
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }
}
