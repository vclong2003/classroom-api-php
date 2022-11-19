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

class UserController extends AbstractController
{
    //GET SINGLE USER
    #[Route('/api/user/{userId}', name: 'app_user_getSingle', methods: ['GET'])]
    public function getSingleUser($userId, UserInfoRepository $userInfoRepo, UserRepository $userRepo, Request $request, SessionRepository $sessionRepo): Response
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $sessionUserId = $authInfo["userId"];

        //Perform authorization
        if ($userId == $sessionUserId) {
            $userInfo = $userInfoRepo->findOneBy(["userId" => $userId]);

            if ($userInfo == null) {
                return new JsonResponse(["msg" => "user not found!"], 404, []);
            } else {
                return new JsonResponse($userInfo, 200, []);
            }
        } else {
            return new JsonResponse(["msg" => "unauthorized!"], 401, []);
        }
    }




    // #[Route('/api/user/change/{userId}', name: 'app_user_change_role', methods: ['POST'])]
    // public function editUser(Request $request, UserRepository $userRepo, SessionRepository $sessionRepo, $userId): Response
    // {
    //     $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
    //     $userId = $authInfo["userId"];
    //     $role = $authInfo["role"];

    //     try {
    //         $data = json_decode($request->getContent(), true);

    //         if ($role == "admin") {
    //             $user = $userRepository->findOneBy(["id" => $userId]);
    //             $user->setRole($data['role']);
    //             $userRepository->save($user, true);
    //             return new JsonResponse(["Message" => "Change Role User Successfully"], 200, []);
    //         }
    //     } catch (\Exception $err) {
    //         return new JsonResponse(["Message" => $err->getMessage()], 400, []);
    //     }
    // }

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
