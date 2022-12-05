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
    public function getSingleUser(UserInfoRepository $userInfoRepo, UserRepository $userRepo, Request $request, SessionRepository $sessionRepo): Response
    {
        try {
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            $userId = $authInfo["userId"];

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

    //UPDATE USER INFO
    // body params: name, dob, phoneNumber, address, imageUrl
    // return: updated user info
    #[Route('/api/user', name: 'app_user_update', methods: ['POST'])]
    public function updateUser(UserInfoRepository $userInfoRepo, UserRepository $userRepo, Request $request, SessionRepository $sessionRepo): Response
    {
        try {
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            $userId = $authInfo["userId"];
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
