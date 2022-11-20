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
use Doctrine\ORM\EntityManagerInterface;
use Error;

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
                return new JsonResponse(["Message" => "user not found!"], 404, []);
            } else {
                return new JsonResponse($userInfo, 200, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["Message" => $err->getMessage()], 400, []);
        }
    }

    //UPDATE USER INFO
    /*
    body params: 
        name, 
        age, 
        phoneNumber, 
        address, 
        imageUrl
    return: updated user info
    */
    // Maybe can move this to ADMIN controller
    #[Route('/api/user', name: 'app_user_update', methods: ['POST'])]
    public function updateUser(UserInfoRepository $userInfoRepo, UserRepository $userRepo, Request $request, SessionRepository $sessionRepo): Response
    {
        try {
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            $userId = $authInfo["userId"];
            $userInfo = $userInfoRepo->findOneBy(["userId" => $userId]);
            $phone = $userInfoRepo->findOneBy(["userId" => $userId])->getPhoneNumber();

            if ($userInfo == null) {
                return new JsonResponse(["Message" => "user not found!"], 404, []);
            } else {
                $data = json_decode($request->getContent(), true); //convert data to associative array
                if ($data["phoneNumber"] != $phone) {
                    $userInfo->setBirthday(\DateTime::createFromFormat('Y/m/d', $data["birthday"]));
                    $userInfo->setPhoneNumber($data["phoneNumber"]);
                    $userInfo->setAddress($data["address"]);
                    $userInfo->setImageUrl($data["imageUrl"]);

                    $userInfoRepo->save($userInfo, true);

                    return new JsonResponse($userInfo, 200, []);
                } else {
                    return new JsonResponse(["Message" => "Invalid phone"], 400, []);
                }
            }
        } catch (\Exception $err) {
            return new JsonResponse(["Message" => $err->getMessage()], 400, []);
        }
    }

    // BELOW FUNCTION WILL BE MOVED TO ADMIN CONTROLLER
    // #[Route('/api/user/remove/{userId}', name: 'app_user_remove', methods: ['GET'])]
    // public function deleteUser(Request $request, UserRepository $userRepository, SessionRepository $sessionRepository, $userId): Response
    // {
    //     try {
    //         $uId = findUserId($request, $sessionRepository);
    //         $role = $userRepository->findOneBy(["id" => $uId])->getRole();

    //         if ($role == "Admin") {
    //             $user = $userRepository->findOneBy(["id" => $userId]);
    //             $userRepository->remove($user);
    //             $userRepository->save($user, true);
    //             return new JsonResponse(["message" => "Delete User Successfully"], 200, []);
    //         }
    //     } catch (\Exception $err) {
    //         return new JsonResponse(["Message" => $err->getMessage()], 400, []);
    //     }
    // }
}