<?php

namespace App\Controller;

use App\Entity\Classroom;
use App\Entity\Student;
use App\Entity\UserInfo;
use App\Repository\ClassroomRepository;
use App\Repository\SessionRepository;
use App\Repository\StudentRepository;
use App\Repository\UserInfoRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ClassroomController extends AbstractController
{
    //CREATE CLASS
    //add classroom, takes "name" param
    #[Route('/api/classroom', name: 'app_classroom_create', methods: ['POST'])]
    public function addClassroom(UserRepository $userRepo, ClassroomRepository $classroomRepo, Request $request, SessionRepository $sessionRepo): Response
    {
        try {
            $data = json_decode($request->getContent(), true); //convert data to associative array
            $userId = getAuthInfo($request, $sessionRepo, $userRepo)["userId"];
            $role = $userRepo->findOneBy(["id" => $userId])->getRole();

            if (strtolower($role) == "teacher") {
                $classroom = new Classroom();
                $classroom->setTeacherId($userId);
                $classroom->setName($data['name']);
                $classroom->setStartDate(date("Y-m-d H:i:s"));
                $classroom->setStudentCount(0);

                $classroomRepo->save($classroom, true);

                return new JsonResponse(["Message" => "Created"], 201, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["Message" => $err->getMessage()], 201, []);
        }
    }

    // GET ALL CLASS
    #[Route('/api/classroom/', name: 'app_classroom_get', methods: ['GET'])]
    public function getClassroom(UserRepository $userRepo, ClassroomRepository $classroomRepo, Request $request, SessionRepository $sessionRepo, UserInfoRepository $userInfoRepo, StudentRepository $studentRepo): Response
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        $dataArray = array();
        if ($role == "teacher") {
            $classrooms = $classroomRepo->findBy(["teacherId" => $userId]);

            foreach ($classrooms as $class) {
                $classArray = $class->jsonSerialize();
                $classArray["teacherName"] = $userInfoRepo->findOneBy(["userId" => $class->getTeacherId()])->getName();
                $classArray["teacherImageUrl"] = $userInfoRepo->findOneBy(["userId" => $class->getTeacherId()])->getImageUrl();
                array_push($dataArray, $classArray);
            }

            return new JsonResponse($dataArray, 200, []);
        } else if ($role == "student") {
            $classrooms = $classroomRepo->findAll();
            foreach ($classrooms as $class) {
                $classId = $class->getId();
                $student = $studentRepo->findOneBy(["classId" => $classId, "userId" => $userId]);

                $classArray = $class->jsonSerialize();
                $classArray["teacherName"] = $userInfoRepo->findOneBy(["userId" => $class->getTeacherId()])->getName();
                $classArray["teacherImageUrl"] = $userInfoRepo->findOneBy(["userId" => $class->getTeacherId()])->getImageUrl();
                $classArray["isJoined"] = ($student == null) ? false : true;

                array_push($dataArray, $classArray);
            }
            return new JsonResponse($dataArray, 200, []);
        }
    }

    //GET SINGLE CLASS INFO
    // take classId, return class info
    #[Route('/api/classroom/{classId}', name: 'app_classroom_getDetail', methods: ['GET'])]
    public function getClassroomDetail(ClassroomRepository $classroomRepo, UserInfoRepository $userInfoRepo, $classId): Response
    {
        $classRoom = $classroomRepo->findOneBy(["id" => $classId]);
        $teacherInfo = $userInfoRepo->findOneBy(["userId" => $classRoom->getTeacherId()]);
        $classRoomInfo = $classRoom->jsonSerialize();
        $classRoomInfo['teacherName'] = $teacherInfo->getName();
        $classRoomInfo['teacherImgURL'] = $teacherInfo->getImageUrl();

        return new JsonResponse($classRoomInfo, 200, []);
    }

    //JOIN THE CLASS
    //takes: classId
    #[Route('/api/classroom/{classId}/student', name: 'app_classroom_addStudent', methods: ['POST'])]
    public function addStudent($classId, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, StudentRepository $studentRepo, ClassroomRepository $classroomRepo): Response
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        $joinedStudent = $studentRepo->findOneBy(["classId" => $classId, "userId" => $userId]);
        if ($joinedStudent != null) { // check for student exsistance, return error msg if exsisted
            return new JsonResponse(["msg" => "already exsisted!"], 409, []);
        } else {
            $class = $classroomRepo->findOneBy(["id" => $classId]);
            if ($class != null) { //check class existance!
                $student = new Student();
                $student->setClassId($classId);
                $student->setUserId($userId);
                $studentRepo->save($student, true);

                $currentStudentCount = $class->getStudentCount();
                $class->setStudentCount($currentStudentCount + 1);
                $classroomRepo->save($class, true);

                return new JsonResponse(["msg" => "ok"], 200, []);
            }
        }
    }

    //GET STUDENT LIST OF THE CLASS
    #[Route('/api/classroom/{classId}/student', name: 'app_classroom_getStudent', methods: ['GET'])]
    public function getStudent($classId, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, StudentRepository $studentRepo, UserInfoRepository $userInfoRepo): Response
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        if ($role == "teacher") {
            $studentList = array();
            $students = $studentRepo->findBy(["classId" => $classId]);
            foreach ($students as $student) {
                $studentId = $student->getUserId();
                $studentInfo = $userInfoRepo->findOneBy(["userId" => $studentId]);

                array_push($studentList, $studentInfo->jsonSerialize());
            }
            return new JsonResponse($studentList, 200, []);
        }
    }

    //REMOVE CLASS
    #[Route('/api/classroom/remove/{classId}', name: 'app_classroom_leave', methods: ['GET'])]
    public function removeClass(Request $request, ClassroomRepository $classroomRepository, UserRepository $userRepo, SessionRepository $sessionRepo, EntityManagerInterface $entityManager, $classId)
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];
        if ($role == "teacher" || $role == "admin") {
            $classRoom = $classroomRepository->findOneBy(["id" => $classId]);
            $classroomRepository->remove($classRoom);
            // $classroomRepository->save($classRoom, true);
            $entityManager->flush();
            return new JsonResponse(["Message" => "Delete Successfully"], 200, []);
        }
    }
}
