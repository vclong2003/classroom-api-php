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
use App\Entity\Assignment;
use App\Repository\PostsRepository;
use App\Repository\UserInfoRepository;
use Doctrine\ORM\EntityManagerInterface;

class PostController extends AbstractController
{
    //Add post
    // take classId
    #[Route('/api/classroom/{classId}/post', name: 'app_post', methods: ['POST'])]
    public function newPost(Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, PostsRepository $postRepo, $classId)
    {
        $data = json_decode($request->getContent(), true); //convert data to associative array
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        //take user role
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

    // #[Route('/api/classroom/{classId}/post/{postId}', name: 'app_post_getDetail', methods: ['GET'])]
    // public function getPostDetail(PostsRepository $postRepo, $postId, $classId): Response
    // {
    //     $classInfo = $postRepo->findOneBy(["id" => $classId]);
    //     $postInfo = $postRepo->findOneBy(["id" => $postId]);
    //     // $teacherInfo = $userInfoRepo->findOneBy(["userId" => $classRoom->getTeacherId()]);
    //     $classRoomInfo = $postInfo->jsonSerialize();

    //     return new JsonResponse($classRoomInfo, 200, []);
    // }

    // take classId
    // return all the post belongs to that classId
    #[Route('/api/classroom/{classId}/post', name: 'app_post_getDetail', methods: ['GET'])]
    public function getPost(UserRepository $userRepo, PostsRepository $postRepo, $classId)
    {
        try {
            $posts = $postRepo->findBy(["classId" => $classId]);
            return new JsonResponse($posts, 200, []);
        } catch (\Exception $err) {
            return new JsonResponse(["Error" => $err->getMessage()], 400, []);
        }
    }

    // take classId and postId, take the new content
    // a statement
    #[Route('/api/classroom/{classId}/post/change/{postId}', name: 'app_post_getDetail', methods: ['POST'])]
    public function editPost(Request $request, UserRepository $userRepo, PostsRepository $postRepo, $classId, $postId)
    {
        $data = json_decode($request->getContent(), true);
        $classInfo = $postRepo->findOneBy(["id" => $classId]);
        $postInfo = $postRepo->findOneBy(["id" => $postId]);
        $postInfo->setContent($data["content"]);
        $postRepo->save($postInfo, true);
        return new JsonResponse(["Message" => "Edit successfully"], 201, []);
    }


    //take classId and postId, find them in the database and remove the post that belongs to postId
    //return a response 
    #[Route('/api/classroom/{classId}/post/change/{postId}', name: 'app_post_delete', methods: ['DELETE'])]
    public function deletePost(Request $request, UserRepository $userRepo, PostsRepository $postRepo, $classId, $postId, EntityManagerInterface $entityManager)
    {
        $data = json_decode($request->getContent(), true);
        $classInfo = $postRepo->findOneBy(["id" => $classId]);
        $postInfo = $postRepo->findOneBy(["id" => $postId]);
        $postRepo->remove($postInfo);
        $entityManager->flush();

        // $postRepo->save($postInfo, true);
        return new JsonResponse(["Message" => "Delete successfully"], 201, []);
    }
}