<?php

namespace App\Controller;

use App\Entity\Classroom;
use App\Repository\ClassroomRepository;
use App\Repository\SessionRepository;
use App\Repository\UserInfoRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClassroomController extends AbstractController
{
    //add classroom, takes "name" param
    #[Route('/api/classroom', name: 'app_classroom_create', methods: ['POST'])]
    public function addClassroom(UserRepository $userRepo, ClassroomRepository $classroomRepo, Request $request, SessionRepository $sessionRepo): Response
    {
        try {
            $data = json_decode($request->getContent(), true); //convert data to associative array
            $userId = findUserId($request, $sessionRepo);
            $role = $userRepo->findOneBy(["id" => $userId])->getRole();

            if ($role == "Teacher") {
                $classroom = new Classroom();
                $classroom->setTeacherId($userId);
                $classroom->setName($data['name']);
                $classroom->setStartDate(time());
                $classroom->setStudentCount(0);

                $classroomRepo->save($classroom, true);

                return new JsonResponse(["msg" => "Created"], 201, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 201, []);
        }
    }

    #[Route('/api/classroom/', name: 'app_classroom_get', methods: ['GET'])]
    public function getClassroom(UserRepository $userRepo, ClassroomRepository $classroomRepo, Request $request, SessionRepository $sessionRepo, UserInfoRepository $userInfoRepo): Response
    {
        $userId = findUserId($request, $sessionRepo);
        $user = $userRepo->findOneBy(["id" => $userId]);
        $role = $user->getRole();

        if ($role == "Teacher") {
            $classrooms = $classroomRepo->findBy(["teacherId" => $user->getId()]);
            $dataArray = array();
            foreach ($classrooms as $class) {
                $classArray = $class->jsonSerialize();
                $classArray["teacherName"] = $userInfoRepo->findOneBy(["userId" => $class->getTeacherId()])->getName();
                $classArray["teacherImageUrl"] = $userInfoRepo->findOneBy(["userId" => $class->getTeacherId()])->getImageUrl();
                array_push($dataArray, $classArray);
            }

            return new JsonResponse($dataArray, 200, []);
        }
    }

    // take classId, return class info
    #[Route('/api/classroom/{classId}', name: 'app_classroom_getDetail', methods: ['GET'])]
    public function getClassroomDetail(UserRepository $userRepo, ClassroomRepository $classroomRepo, Request $request, SessionRepository $sessionRepo, UserInfoRepository $userInfoRepo, $classId): Response
    {

        $classRoom = $classroomRepo->findOneBy(["id" => $classId]);
        $userInfo = $userInfoRepo->findOneBy(["id" => $classId]);
        // $classRoomInfo = array();
        // foreach ($classRoom as $info) {
        //     $classDetail = $info->jsonSerialize();
        //     $classDetail["id"] = $classroomRepo->findOneBy(["id" => $info -> getId()])->getId();
        //     $classDetail["teacherId"] =$classroomRepo->findOneBy(["id" => $info -> getId()])->getTeacherId();
        //     $classDetail["name"] = $classroomRepo->findOneBy(["id" => $info -> getId()])->getName();
        //     $classDetail["startDate"] = $classroomRepo->findOneBy(["id" => $info -> getId()])->getStartDate();
        //     $classDetail["studentDate"] = $classroomRepo->findOneBy(["id" => $info -> getId()])->getStartDate()
        //     $classArray["teacherName"] = $userInfoRepo->findOneBy(["userId" => $class->getTeacherId()])->getName();
        //         $classArray["teacherImageUrl"] = $userInfoRepo->findOneBy(["userId" => $class->getTeacherId()])->getImageUrl();
        //     array_push($classRoomInfo, $classDetail);
        // }
        // return new JsonResponse($classRoomInfo, 200, []);

        return new JsonResponse(["id" => $classRoom->getId(), "teacherId" => $classRoom->getTeacherId(), "name" => $classRoom->getName(), "startDate" => $classRoom->getStartDate(), "studentCount" => $classRoom->getStudentCount(), "teacherName" => $userInfo->getName(), "teacherImgURL" => $userInfo->getImageUrl()], 200, []);
    }
}