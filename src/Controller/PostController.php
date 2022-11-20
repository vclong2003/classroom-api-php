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
    //GET ALL POST IN A CLASS
    // takes: classId
    #[Route('/api/classroom/{classId}/post', name: 'app_post_get', methods: ['POST'])]
    public function newPost($classId, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, PostsRepository $postRepo, ClassroomRepository $classRepo, StudentRepository $studentRepo): Response
    {
        // $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        // $userId = $authInfo["userId"];

        // $class = $classRepo->findOneBy(["id" => $classId]);
        // if ($class == null) {
        //     return new JsonResponse(["msg" => "not found"], 404, []);
        // } else {
        //     $student = $studentRepo->findOneBy(["userId" => $userId, "classId" => $classId]);
        //     $teacherId = $class->getTeacherId();

        //     // if user is teacher or student of the class return result. Else, return unauth respone 
        //     if ($student != null || $teacherId == $userId) {
        //         $posts = $postRepo->findAll(["classId" => $classId]);
        //         return new JsonResponse($posts, 200, []);
        //     } else {
        //         return new JsonResponse(["msg" => "unauthorized!"], 401, []);
        //     }
        // }
        try {
            $data = json_decode($request->getContent(), true); //convert data to associative array
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            $userId = $authInfo["userId"];
            $class = $classRepo->findOneBy(["id" => $classId]);
            //take user role
            $role = $authInfo["role"];

            if ($role == "admin" || $role == "teacher") {
                $post = new Posts();
                $post->setUserId($userId);
                $post->setClassId($classId);
                $post->setIsAssignment($data["isAssignment"]);
                $post->setContent($data["content"]);
                $post->setSubmitCount(0);
                $post->setDateAdded(date("Y-m-d H:i:s"));

                $postRepo->save($post, true);
                return new JsonResponse(["Message" => "A new post has been added"], 201, []);
            } else if ($class == null) {
                return new JsonResponse(["Message" => "Class not found"], 404, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["Message" => $err->getMessage()], 400, []);
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
    public function getPost(UserRepository $userRepo, PostsRepository $postRepo, $classId, Request $request, SessionRepository $sessionRepo, ClassroomRepository $classRepo, StudentRepository $studentRepo)
    {

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