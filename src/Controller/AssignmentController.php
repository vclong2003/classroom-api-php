<?php

namespace App\Controller;

use App\Repository\AssignmentRepository;
use App\Repository\PostsRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssignmentController extends AbstractController
{
    //GET ASMs
    //takes: postId
    #[Route('/api/classroom/{classId}/post/{postId}/assignment', name: 'app_asm_getAll', methods: ["GET"])]
    public function getAssignment($postId, $classId, PostsRepository $postRepo, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, AssignmentRepository $asmRepo): Response
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        if ($role != "teacher") {
            return new JsonResponse(["msg" => 'unauthorized!'], 401, []);
        }

        $post = $postRepo->findOneBy(["id" => $postId]);
        if ($post == null) {
            return new JsonResponse(["msg" => 'post not found'], 404, []);
        }

        $asm = $asmRepo->findBy(["postId" => $postId]);
        return new JsonResponse($asm, 200, []);
    }

    //ADD ASM
    //takes: postId
    #[Route('/api/classroom/{classId}/post/{postId}/assignment', name: 'app_asm_add', methods: ["POST"])]
    public function addAssignment($postId, PostsRepository $postRepo, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, AssignmentRepository $asmRepo): Response
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        $post = $postRepo->findOneBy(["id" => $postId]);
        if ($post == null) {
            return new JsonResponse(["msg" => 'post not found'], 404, []);
        }



        $asm = $asmRepo->findBy(["postId" => $postId]);
        return new JsonResponse($asm, 200, []);
    }
}
