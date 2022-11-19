<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Entity\Posts;
use App\Repository\PostsRepository;

class PostController extends AbstractController
{
    #[Route('/post/{classId}', name: 'app_post', methods: ['POST'])]
    public function newPost(Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, PostsRepository $postRepo, $classId)
    {
        $data = json_decode($request->getContent(), true); //convert data to associative array
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        if ($role == "admin") {
            $post = new Posts();
            $post->setUserId($userId);
            $post->setClassId($classId);
            $post->setIsAssignment($data["isAssignment"]);
            $post->setContent($data["content"]);
            $post->setCommentCount(0);
            $post->setSubmitCount(0);
            $post->setDateAdded(date("Y-m-d H:i:s"));

            $postRepo->save($post, true);
            return new JsonResponse(["Message" => "A new post has been added"], 201, []);
        }
    }

    #[Route('post/{postId}', name: 'app_post_getDetail', methods: ['GET'])]
    public function getPostDetail(PostsRepository $postRepo, $postId): Response
    {
        $postInfo = $postRepo->findOneBy(["id" => $postId]);
        // $teacherInfo = $userInfoRepo->findOneBy(["userId" => $classRoom->getTeacherId()]);
        $classRoomInfo = $postInfo->jsonSerialize();

        return new JsonResponse($classRoomInfo, 200, []);
    }
}