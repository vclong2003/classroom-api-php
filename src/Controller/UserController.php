<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use App\Repository\UserInfoRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends AbstractController
{

    //GET SINGLE USER
    #[Route('/api/user', name: 'app_user_getSingle', methods: ['GET'])]
    public function getSingleUser(
        UserInfoRepository $userInfoRepo,
        UserRepository $userRepo,
        Request $request,
        SessionRepository $sessionRepo
    ): Response {
        try {
            $authInfo = Utils::getAuthInfo($request, $sessionRepo, $userRepo);
            if ($authInfo == null) {
                return new JsonResponse(["msg" => 'unauthorized!'], 401, []);
            }
            $userId = $authInfo->getId();

            $userInfo = $userInfoRepo->findOneBy(["userId" => $userId]);
            if ($userInfo == null) {
                return new JsonResponse(["msg" => "user not found!"], 404, []);
            } else {
                return new JsonResponse($userInfo, 200, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    //CHANGE ROLE
    //body param: role
    #[Route('/api/user/role', name: 'app_user_changeRole', methods: ['POST'])]
    public function changeRole(
        UserRepository $userRepo,
        Request $request,
        SessionRepository $sessionRepo
    ): Response {
        try {
            $predefinedRole = ['student', 'teacher'];
            $authInfo = Utils::getAuthInfo($request, $sessionRepo, $userRepo);
            if ($authInfo == null) {
                return new JsonResponse(["msg" => 'unauthorized!'], 401, []);
            }

            $data = json_decode($request->getContent(), true);
            $roleToSet = $data['role'];
            if ($roleToSet == null || !in_array($roleToSet, $predefinedRole)) {
                return new JsonResponse(['msg' => 'role not valid'], 406, []);
            }
            $authInfo->setRole($roleToSet);
            $userRepo->save($authInfo, true);

            return new JsonResponse(['msg' => 'role set'], 200, []);
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    //UPDATE USER INFO
    // body params: name, dob, phoneNumber, address, imageUrl
    // return: updated user info
    #[Route('/api/user', name: 'app_user_update', methods: ['POST'])]
    public function updateUser(
        UserInfoRepository $userInfoRepo,
        UserRepository $userRepo,
        Request $request,
        SessionRepository $sessionRepo
    ): Response {
        try {
            $authInfo = Utils::getAuthInfo($request, $sessionRepo, $userRepo);
            if ($authInfo == null) {
                return new JsonResponse(["msg" => 'unauthorized!'], 401, []);
            }
            $userId = $authInfo->getId();
            $userInfo = $userInfoRepo->findOneBy(["userId" => $userId]);

            if ($userInfo == null) {
                return new JsonResponse(["Message" => "user not found!"], 404, []);
            }

            $data = json_decode($request->getContent(), true); //convert data to associative array
            $userInfo->setName($data['name']);
            $userInfo->setDob($data["dob"]);
            $userInfo->setPhoneNumber($data["phoneNumber"]);
            $userInfo->setAddress($data["address"]);
            $userInfo->setImageUrl($data["imageUrl"]);
            $userInfoRepo->save($userInfo, true);

            return new JsonResponse($userInfo, 200, []);
        } catch (\Exception $err) {
            return new JsonResponse(["Message" => $err->getMessage()], 400, []);
        }
    }
}
