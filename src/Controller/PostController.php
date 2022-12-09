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
use App\Repository\AssignmentRepository;
use App\Repository\ClassroomRepository;
use App\Repository\PostsRepository;
use App\Repository\StudentRepository;

class PostController extends AbstractController
{
    //GET POST
    // take: classId
    #[Route('/api/classroom/{classId}/post', name: 'app_post_get', methods: ['GET'])]
    public function getPost(
        $classId,
        UserRepository $userRepo,
        PostsRepository $postRepo,
        Request $request,
        SessionRepository $sessionRepo,
        ClassroomRepository $classRepo,
        StudentRepository $studentRepo,
        AssignmentRepository $asmRepo
    ) {
        try {
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            if ($authInfo == null) {
                return new JsonResponse(["msg" => 'unauthorized!'], 401, []);
            }
            $userId = $authInfo->getId();
            $role = $authInfo->getRole();

            $class = $classRepo->findOneBy(["id" => $classId]);
            if ($class == null) {
                return new JsonResponse(["msg" => "not found"], 404, []);
            }

            $teacherId = $class->getTeacherId();

            if ($role == 'teacher') {
                if ($teacherId != $userId) {
                    return new JsonResponse(['msg' => 'not your class'], 401, []);
                }
                $posts = $postRepo->findBy(["classId" => $classId], ['dateAdded' => 'DESC']);
                return new JsonResponse($posts, 200, []);
            }

            if ($role == 'student') {
                $student = $studentRepo->findOneBy(["userId" => $userId, "classId" => $classId]);
                if ($student == null) {
                    return new JsonResponse(['msg' => 'not your class'], 401, []);
                }

                $dataArray = array();
                $posts = $postRepo->findBy(["classId" => $classId], ['dateAdded' => 'DESC']);
                foreach ($posts as $post) {
                    $postData = $post->jsonSerialize();
                    if ($post->isIsAssignment()) {
                        $asm = $asmRepo->findOneBy(["postId" => $post->getId(), "userId" => $userId]);
                        $postData['asmId'] = $asm == null ? null : $asm->getId();
                    }
                    array_push($dataArray, $postData);
                }

                return new JsonResponse($dataArray, 200, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    //GET SINGLE POST
    // take: classId
    #[Route('/api/classroom/{classId}/post/{postId}', name: 'app_post_getSingle', methods: ['GET'])]
    public function getSinglePost(
        $classId,
        $postId,
        UserRepository $userRepo,
        PostsRepository $postRepo,
        Request $request,
        SessionRepository $sessionRepo,
        ClassroomRepository $classRepo,
        StudentRepository $studentRepo,
        AssignmentRepository $asmRepo
    ) {
        try {
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            if ($authInfo == null) {
                return new JsonResponse(["msg" => 'unauthorized!'], 401, []);
            }
            $userId = $authInfo->getId();
            $role = $authInfo->getRole();

            $class = $classRepo->findOneBy(["id" => $classId]);
            if ($class == null) {
                return new JsonResponse(["msg" => "not found"], 404, []);
            }

            $teacherId = $class->getTeacherId();

            if ($role == 'teacher') {
                if ($teacherId != $userId) {
                    return new JsonResponse(['msg' => 'not your class'], 401, []);
                }

                $post = $postRepo->findOneBy(["classId" => $classId, 'id' => $postId]);
                if ($post == null) {
                    return new JsonResponse(['msg' => 'post not found'], 404, []);
                }

                return new JsonResponse($post, 200, []);
            }

            if ($role == 'student') {
                $student = $studentRepo->findOneBy(["userId" => $userId, "classId" => $classId]);
                if ($student == null) {
                    return new JsonResponse(['msg' => 'not your class'], 401, []);
                }

                $post = $postRepo->findOneBy(["classId" => $classId, 'id' => $postId]);
                if ($post == null) {
                    return new JsonResponse(['msg' => 'post not found'], 404, []);
                }

                $postDataArray = $post->jsonSerialize();
                if ($post->isIsAssignment()) {
                    $asm = $asmRepo->findOneBy(["postId" => $post->getId(), "userId" => $userId]);
                    $postDataArray['asmId'] = $asm == null ? null : $asm->getId();
                }

                return new JsonResponse($postDataArray, 200, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    // ADD POST
    // takes: classId
    // body params: isAssignment, content
    #[Route('/api/classroom/{classId}/post', name: 'app_post_add', methods: ['POST'])]
    public function addPost(
        $classId,
        Request $request,
        SessionRepository $sessionRepo,
        UserRepository $userRepo,
        PostsRepository $postRepo,
        ClassroomRepository $classRepo
    ): Response {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        if ($authInfo == null) {
            return new JsonResponse(["msg" => 'unauthorized!'], 401, []);
        }
        $userId = $authInfo->getId();
        $role = $authInfo->getRole();

        if ($role != "teacher") {
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
    public function updatePost(
        $classId,
        $postId,
        Request $request,
        UserRepository $userRepo,
        PostsRepository $postRepo,
        SessionRepository $sessionRepo,
        ClassroomRepository $classRepo
    ) {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        if ($authInfo == null) {
            return new JsonResponse(["msg" => 'unauthorized!'], 401, []);
        }
        $userId = $authInfo->getId();
        $role = $authInfo->getRole();

        if ($role != "teacher") {
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
    public function deletePost(
        Request $request,
        SessionRepository $sessionRepo,
        UserRepository $userRepo,
        PostsRepository $postRepo,
        $classId,
        $postId,
        ClassroomRepository $classRepo
    ) {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        if ($authInfo == null) {
            return new JsonResponse(["msg" => 'unauthorized!'], 401, []);
        }
        $userId = $authInfo->getId();
        $role = $authInfo->getRole();

        if ($role != "teacher") {
            return new JsonResponse(["msg" => "unauthorized!"], 401, []);
        }

        try {
            $class = $classRepo->findOneBy(["id" => $classId]);
            if ($class == null) {
                return new JsonResponse(["msg" => "class not found!"], 404, []);
            }
            if ($class->getTeacherId() != $userId) {
                return new JsonResponse(["msg" => "not your class!"], 401, []);
            }

            $post = $postRepo->findOneBy(["classId" => $classId, "id" => $postId]);
            if ($post == null) {
                return new JsonResponse(["msg" => "post not found"], 404, []);
            }

            $postRepo->remove($post, true);

            return new JsonResponse(["msg" => "deleted"], 200, []);
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }
}
