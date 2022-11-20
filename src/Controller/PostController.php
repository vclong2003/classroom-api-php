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
use App\Repository\ClassroomRepository;
use App\Repository\PostsRepository;
use App\Repository\StudentRepository;
use App\Repository\UserInfoRepository;
use Doctrine\ORM\EntityManagerInterface;

class PostController extends AbstractController
{
    //GET POST
    // take: classId
    #[Route('/api/classroom/{classId}/post', name: 'app_post_get', methods: ['GET'])]
    public function getPost(UserRepository $userRepo, PostsRepository $postRepo, $classId, Request $request, SessionRepository $sessionRepo, ClassroomRepository $classRepo, StudentRepository $studentRepo)
    {
        try {
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            $userId = $authInfo["userId"];
            $class = $classRepo->findOneBy(["id" => $classId]);

            if ($class == null) {
                return new JsonResponse(["msg" => "not found"], 404, []);
            } else {
                $student = $studentRepo->findOneBy(["userId" => $userId, "classId" => $classId]);
                $teacherId = $class->getTeacherId();

                // if user is teacher or student of the class return result. Else, return unauth respone 
                if ($student != null || $teacherId == $userId) {
                    $posts = $postRepo->findAll(["classId" => $classId]);
                    return new JsonResponse($posts, 200, []);
                } else {
                    return new JsonResponse(["msg" => "unauthorized!"], 401, []);
                }
            }
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    // ADD POST
    // takes: classId
    #[Route('/api/classroom/{classId}/post', name: 'app_post_add', methods: ['POST'])]
    public function newPost($classId, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, PostsRepository $postRepo, ClassroomRepository $classRepo, StudentRepository $studentRepo): Response
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        try {
            $data = json_decode($request->getContent(), true); //convert data to associative array

            $class = $classRepo->findOneBy(["id" => $classId]);

            if ($class == null) {
                return new JsonResponse(["msg" => "Class not found"], 404, []);
            } else {
                if ($role == "admin" || $role == "teacher") {
                    $post = new Posts();
                    $post->setUserId($userId);
                    $post->setClassId($classId);
                    $post->setIsAssignment($data["isAssignment"]);
                    $post->setContent($data["content"]);
                    $post->setSubmitCount(0);
                    $post->setDateAdded(date("Y-m-d H:i:s"));

                    $postRepo->save($post, true);
                    return new JsonResponse(["msg" => "A new post has been added"], 201, []);
                } else {
                    return new JsonResponse(["msg" => "unauthorized!"], 401, []);
                }
            }
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    // take classId and postId, take the new content
    // a statement
    #[Route('/api/classroom/{classId}/post/{postId}', name: 'app_post_getDetail', methods: ['POST'])]
    public function editPost(Request $request, UserRepository $userRepo, PostsRepository $postRepo, $classId, $postId, SessionRepository $sessionRepo)
    {
        try {
            $data = json_decode($request->getContent(), true);
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            $classInfo = $postRepo->findOneBy(["id" => $classId]);
            $postInfo = $postRepo->findOneBy(["id" => $postId]);
            $role = $authInfo["role"];

            if ($classInfo == null) {
                return new JsonResponse(["Message" => "Class Not Found"], 404, []);
            } else if ($postInfo == null) {
                return new JsonResponse(["Message" => "Post Not Found"], 404, []);
            } else if ($role == "teacher" || $role == "admin") {
                // $postRepo->remove($postInfo);
                // $postRepo->save($postInfo, true);
                // return new JsonResponse(["Message" => "Delete successfully"], 201, []);
                $postInfo->setContent($data["content"]);
                $postRepo->save($postInfo, true);
                return new JsonResponse(["Message" => "Edit successfully"], 201, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["Message" => $err->getMessage()], 400, []);
        }
    }


    //take classId and postId, find them in the database and remove the post that belongs to postId
    //return a response 
    #[Route('/api/classroom/{classId}/post/change/{postId}', name: 'app_post_delete', methods: ['DELETE'])]
    public function deletePost(Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, PostsRepository $postRepo, $classId, $postId, EntityManagerInterface $entityManager, ClassroomRepository $classRepo)
    {
        try {
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            $classInfo = $postRepo->findOneBy(["id" => $classId]);
            $postInfo = $postRepo->findOneBy(["id" => $postId]);
            $role = $authInfo["role"];

            if ($classInfo == null) {
                return new JsonResponse(["Message" => "Class Not Found"], 404, []);
            } else if ($postInfo == null) {
                return new JsonResponse(["Message" => "Post Not Found"], 404, []);
            } else if ($role == "teacher" || $role == "admin") {
                $postRepo->remove($postInfo);
                $entityManager->flush();
                // $postRepo->save($postInfo, true);
                return new JsonResponse(["Message" => "Delete successfully"], 201, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["Message" => $err->getMessage()], 400, []);
        }
    }
}
