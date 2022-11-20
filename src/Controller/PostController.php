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
use App\Repository\ClassroomRepository;
use App\Repository\PostsRepository;
use App\Repository\StudentRepository;
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
    // body params: isAssignment, content
    #[Route('/api/classroom/{classId}/post', name: 'app_post_add', methods: ['POST'])]
    public function addPost($classId, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, PostsRepository $postRepo, ClassroomRepository $classRepo): Response
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        if ($role != "admin" && $role != "teacher") {
            return new JsonResponse(["msg" => "unauthorized!"], 401, []);
        }

        try {
            $class = $classRepo->findOneBy(["id" => $classId]);

            if ($class == null) {
                return new JsonResponse(["msg" => "Class not found"], 404, []);
            } else {
                $data = json_decode($request->getContent(), true); //convert data to associative array

                $post = new Posts();
                $post->setUserId($userId);
                $post->setClassId($classId);
                $post->setIsAssignment($data["isAssignment"]);
                $post->setContent($data["content"]);
                $post->setSubmitCount(0);
                $post->setDateAdded(date("Y-m-d H:i:s"));
                $postRepo->save($post, true);

                return new JsonResponse(["msg" => "added"], 201, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    // UPDATE POST
    // takes: classId, postId
    // body params: isAssignment, content
    #[Route('/api/classroom/{classId}/post/{postId}', name: 'app_post_update', methods: ['POST'])]
    public function updatePost($classId, $postId, Request $request, UserRepository $userRepo, PostsRepository $postRepo, SessionRepository $sessionRepo, ClassroomRepository $classRepo)
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        if ($role != "admin" && $role != "teacher") {
            return new JsonResponse(["msg" => "unauthorized!"], 401, []);
        }

        try {
            $class = $classRepo->findOneBy(["id" => $classId]);
            $post = $postRepo->findOneBy(["classId" => $classId, "id" => $postId]);
            if ($class == null || $post == null) {
                return new JsonResponse(["msg" => "class or post not found"], 404, []);
            } else {
                if ($userId != $post->getUserId()) {
                    return new JsonResponse(["msg" => "unauthorized!"], 401, []);
                }
                $data = json_decode($request->getContent(), true); //convert data to associative array

                $post->setIsAssignment($data["isAssignment"]);
                $post->setContent($data["content"]);
                $postRepo->save($post, true);

                return new JsonResponse(["msg" => "updated"], 200, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }


    //DELETE POST
    //takes: classId and postId
    #[Route('/api/classroom/{classId}/post/{postId}', name: 'app_post_delete', methods: ['DELETE'])]
    public function deletePost(Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, PostsRepository $postRepo, $classId, $postId, ClassroomRepository $classRepo)
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        if ($role != "admin" && $role != "teacher") {
            return new JsonResponse(["msg" => "unauthorized!"], 401, []);
        }

        try {
            $class = $classRepo->findOneBy(["id" => $classId]);
            $post = $postRepo->findOneBy(["classId" => $classId, "id" => $postId]);
            if ($class == null || $post == null) {
                return new JsonResponse(["msg" => "class or post not found"], 404, []);
            } else {
                if ($userId != $post->getUserId()) {
                    return new JsonResponse(["msg" => "unauthorized!"], 401, []);
                }

                $postRepo->remove($post, true);

                return new JsonResponse(["msg" => "deleted"], 200, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }
}
