<?php

namespace App\Controller;

use App\Entity\Assignment;
use App\Repository\AssignmentRepository;
use App\Repository\ClassroomRepository;
use App\Repository\PostsRepository;
use App\Repository\SessionRepository;
use App\Repository\StudentRepository;
use App\Repository\UserInfoRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssignmentController extends AbstractController
{
    //GET ASMs
    //takes: classId, postId
    #[Route('/api/classroom/{classId}/post/{postId}/assignment', name: 'app_asm_get', methods: ["GET"])]
    public function getAssignment($postId, $classId, PostsRepository $postRepo, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, AssignmentRepository $asmRepo, ClassroomRepository $classRepo, UserInfoRepository $userInfoRepo): Response
    {
        try {
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            $userId = $authInfo["userId"];
            $role = $authInfo["role"];

            if ($role != "teacher") {
                return new JsonResponse(["msg" => 'unauthorized!'], 401, []);
            }

            $class = $classRepo->findOneBy(["id" => $classId]);
            if ($class == null) {
                return new JsonResponse(['msg' => 'class not found'], 404, []);
            }
            if ($class->getTeacherId() != $userId) {
                return new JsonResponse(['msg' => 'not your class'], 401, []);
            }

            $post = $postRepo->findOneBy(["id" => $postId]);
            if ($post == null) {
                return new JsonResponse(["msg" => 'post not found'], 404, []);
            }

            $dataArray = array();
            $asms = $asmRepo->findBy(["postId" => $postId]);
            foreach ($asms as $asm) {
                $userInfo = $userInfoRepo->findOneBy(["userId" => $asm->getUserId()]);
                $asmData = $asm->jsonSerialize();
                $asmData['userName'] = $userInfo->getName();
                $asmData['userImageUrl'] = $userInfo->getImageUrl();

                array_push($dataArray, $asmData);
            }
            return new JsonResponse($dataArray, 200, []);
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    //GET SINGLE ASM
    //takes: classId, postId
    #[Route('/api/classroom/{classId}/post/{postId}/assignment/{asmId}', name: 'app_asm_getSingle', methods: ["GET"])]
    public function getSingleAssignment($postId, $classId, $asmId, PostsRepository $postRepo, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, AssignmentRepository $asmRepo, ClassroomRepository $classRepo): Response
    {
        try {
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            $userId = $authInfo["userId"];
            $role = $authInfo["role"];

            $class = $classRepo->findOneBy(["id" => $classId]);
            if ($class == null) {
                return new JsonResponse(['msg' => 'class not found'], 404, []);
            }

            $post = $postRepo->findOneBy(["id" => $postId]);
            if ($post == null) {
                return new JsonResponse(["msg" => 'post not found'], 404, []);
            }

            $asm = $asmRepo->findOneBy(["id" => $asmId]);
            if ($asm == null) {
                return new JsonResponse(["msg" => 'asm not found'], 404, []);
            }

            return new JsonResponse($asm, 200, []);
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    //ADD ASM
    //takes: classId, postId
    //body param: fileUrl
    #[Route('/api/classroom/{classId}/post/{postId}/assignment', name: 'app_asm_add', methods: ["POST"])]
    public function addAssignment($classId, $postId, PostsRepository $postRepo, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, AssignmentRepository $asmRepo, ClassroomRepository $classRepo, StudentRepository $studentRepo): Response
    {
        try {
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            $userId = $authInfo["userId"];
            $role = $authInfo["role"];

            $class = $classRepo->findOneBy(["id" => $classId]);
            if ($class == null) {
                return new JsonResponse(['msg' => 'class not found'], 404, []);
            }

            $student = $studentRepo->findOneBy(["classId" => $classId, "userId" => $userId]);
            if ($student == null) {
                return new JsonResponse(['msg' => 'not your class'], 401, []);
            }

            $post = $postRepo->findOneBy(["id" => $postId]);
            if ($post == null) {
                return new JsonResponse(["msg" => 'post not found'], 404, []);
            }

            $data = json_decode($request->getContent(), true); //convert data to associative array
            $asm = new Assignment();
            $asm->setUserId($userId);
            $asm->setPostId($postId);
            $asm->setFileUrl($data["fileUrl"]);
            $asm->setDateAdded(date("Y-m-d H:i:s"));
            $asmRepo->save($asm, true);

            return new JsonResponse(['msg' => 'created'], 201, []);
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    //EDIT ASM
    //takes: classId, postId, asmId
    //body param: fileUrl
    #[Route('/api/classroom/{classId}/post/{postId}/assignment/{asmId}', name: 'app_asm_edit', methods: ["POST"])]
    public function editAssignment($classId, $postId, $asmId, PostsRepository $postRepo, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, AssignmentRepository $asmRepo, ClassroomRepository $classRepo, StudentRepository $studentRepo): Response
    {
        try {
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            $userId = $authInfo["userId"];
            $role = $authInfo["role"];

            $class = $classRepo->findOneBy(["id" => $classId]);
            if ($class == null) {
                return new JsonResponse(['msg' => 'class not found'], 404, []);
            }

            $student = $studentRepo->findOneBy(["classId" => $classId, "userId" => $userId]);
            if ($student == null) {
                return new JsonResponse(['msg' => 'not your class'], 401, []);
            }

            $post = $postRepo->findOneBy(["id" => $postId]);
            if ($post == null) {
                return new JsonResponse(["msg" => 'post not found'], 404, []);
            }

            $data = json_decode($request->getContent(), true); //convert data to associative array
            $asm = $asmRepo->findOneBy(['id' => $asmId]);
            if ($asm == null) {
                return new JsonResponse(['msg' => 'asm not found'], 404, []);
            }
            if ($asm->getUserId() != $userId) {
                return new JsonResponse(['msg' => 'not your asm'], 401, []);
            }

            $asm->setFileUrl($data["fileUrl"]);
            $asm->setDateAdded(date("Y-m-d H:i:s"));
            $asmRepo->save($asm, true);

            return new JsonResponse(['msg' => 'updated'], 200, []);
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    //DELETE ASM
    //takes: classId, postId, asmId
    #[Route('/api/classroom/{classId}/post/{postId}/assignment/{asmId}', name: 'app_asm_delete', methods: ["DELETE"])]
    public function deleteAssignment($classId, $postId, $asmId, PostsRepository $postRepo, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, AssignmentRepository $asmRepo, ClassroomRepository $classRepo, StudentRepository $studentRepo): Response
    {
        try {
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            $userId = $authInfo["userId"];
            $role = $authInfo["role"];

            $class = $classRepo->findOneBy(["id" => $classId]);
            if ($class == null) {
                return new JsonResponse(['msg' => 'class not found'], 404, []);
            }

            $student = $studentRepo->findOneBy(["classId" => $classId, "userId" => $userId]);
            if ($student == null) {
                return new JsonResponse(['msg' => 'not your class'], 401, []);
            }

            $post = $postRepo->findOneBy(["id" => $postId]);
            if ($post == null) {
                return new JsonResponse(["msg" => 'post not found'], 404, []);
            }

            $asm = $asmRepo->findOneBy(['id' => $asmId]);
            if ($asm == null) {
                return new JsonResponse(['msg' => 'asm not found'], 404, []);
            }
            if ($asm->getUserId() != $userId) {
                return new JsonResponse(['msg' => 'not your asm'], 401, []);
            }

            $asmRepo->remove($asm, true);

            return new JsonResponse(['msg' => 'deleted'], 200, []);
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }

    //SET ASM MARK
    //takes: classId, postId, asmId
    //body param: 'mark'
    #[Route('/api/classroom/{classId}/post/{postId}/assignment/{asmId}/mark', name: 'app_asm_setMark', methods: ["POST"])]
    public function setAsmMark($classId, $postId, $asmId, PostsRepository $postRepo, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, AssignmentRepository $asmRepo, ClassroomRepository $classRepo): Response
    {
        try {
            $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
            $userId = $authInfo["userId"];
            $role = $authInfo["role"];

            if ($role != 'teacher') {
                return new JsonResponse(['msg' => 'unauthorized!'], 401, []);
            }

            $class = $classRepo->findOneBy(["id" => $classId]);
            if ($class == null) {
                return new JsonResponse(['msg' => 'class not found'], 404, []);
            }
            if ($class->getTeacherId() != $userId) {
                return new JsonResponse(['msg' => 'not your class'], 401, []);
            }

            $post = $postRepo->findOneBy(["id" => $postId]);
            if ($post == null) {
                return new JsonResponse(["msg" => 'post not found'], 404, []);
            }

            $asm = $asmRepo->findOneBy(['id' => $asmId]);
            if ($asm == null) {
                return new JsonResponse(['msg' => 'asm not found'], 404, []);
            }

            $data = json_decode($request->getContent(), true); //convert data to associative array
            $asm->setMark($data['mark']);

            $asmRepo->save($asm, true);

            return new JsonResponse(['msg' => 'mark set'], 200, []);
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }
}

//tested