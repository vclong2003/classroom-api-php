<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Entity\ClassSession;
use App\Repository\AttendanceRepository;
use App\Repository\ClassroomRepository;
use App\Repository\ClassSessionRepository;
use App\Repository\SessionRepository;
use App\Repository\UserInfoRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AttendanceController extends AbstractController
{
    //ADD ATTENDANCES RECORD
    //takes: classId
    //body params: <studentId> : <isAttend> - Example: {"1": true, "9": true, "10": true,...}
    #[Route('/api/classroom/{classId}/classSession', name: 'app_attendances_add', methods: ['POST'])]
    public function addAttendances($classId, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, ClassroomRepository $classRepo, ClassSessionRepository $classSessionRepo, AttendanceRepository $attendanceRepo)
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        if ($role != 'teacher') {
            return new JsonResponse(['msg' => 'unauthorized'], 401, []);
        }

        $class = $classRepo->findOneBy(['id' => $classId]);
        if ($class == null) {
            return new JsonResponse(['msg' => 'class not found'], 404, []);
        }
        if ($class->getTeacherId() != $userId) {
            return new JsonResponse(['msg' => 'not your class'], 404, []);
        }

        $data = json_decode($request->getContent(), true); //convert data to associative array
        if ($data == null) {
            return new JsonResponse(['msg' => 'data not found'], 400, []);
        }

        $classSession = new ClassSession();
        $classSession->setTime(date("Y-m-d H:i:s"));
        $classSession->setClassId($classId);
        $classSessionRepo->save($classSession, true);
        $classSessionId = $classSession->getId();

        foreach ($data as $studentId => $isAttend) {
            $attendance = new Attendance();
            $attendance->setClassSessionId($classSessionId);
            $attendance->setUserId($studentId);
            $attendance->setIsAttend($isAttend);
            $attendanceRepo->save($attendance, true);
        }

        return new JsonResponse(['msg' => 'created'], 201, []);
    }

    //GET CLASS_SESSIONS (ATTENDANCES GROUP)
    //takes: classId
    #[Route(
        '/api/classroom/{classId}/classSession',
        name: 'app_classSession_get',
        methods: ['GET']
    )]
    public function getClassSession($classId, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, ClassroomRepository $classRepo, ClassSessionRepository $classSessionRepo, AttendanceRepository $attendanceRepo)
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        if ($role != 'teacher') {
            return new JsonResponse(['msg' => 'unauthorized'], 401, []);
        }

        $class = $classRepo->findOneBy(['id' => $classId]);
        if ($class == null) {
            return new JsonResponse(['msg' => 'class not found'], 404, []);
        }
        if ($class->getTeacherId() != $userId) {
            return new JsonResponse(['msg' => 'not your class'], 404, []);
        }

        $classSessions = $classSessionRepo->findBy(['classId' => $classId]);

        return new JsonResponse($classSessions, 200, []);
    }

    //GET ATTENDANCES
    //takes: classId, classSessionId
    #[Route(
        '/api/classroom/{classId}/classSession/{classSessionId}/attendances',
        name: 'app_attendances_get',
        methods: ['GET']
    )]
    public function getAttendances(
        $classId,
        $classSessionId,
        Request $request,
        SessionRepository $sessionRepo,
        UserRepository $userRepo,
        ClassroomRepository $classRepo,
        ClassSessionRepository $classSessionRepo,
        AttendanceRepository $attendanceRepo,
        UserInfoRepository $userInfoRepo
    ) {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        if ($role != 'teacher') {
            return new JsonResponse(['msg' => 'unauthorized'], 401, []);
        }

        $class = $classRepo->findOneBy(['id' => $classId]);
        if ($class == null) {
            return new JsonResponse(['msg' => 'class not found'], 404, []);
        }
        if ($class->getTeacherId() != $userId) {
            return new JsonResponse(['msg' => 'not your class'], 401, []);
        }

        $classSession = $classSessionRepo->findOneBy(['id' => $classSessionId]);
        if ($classSession == null) {
            return new JsonResponse(['msg' => 'class session not found'], 404, []);
        }

        $dataArray = array();
        $attendances = $attendanceRepo->findBy(['classSessionId' => $classSession->getId()]);
        foreach ($attendances as $attendance) {
            $userInfo = $userInfoRepo->findOneBy(['userId' => $attendance->getUserId()]);
            $attendanceData = $attendance->jsonSerialize();
            // temporary, wont't work with mock data
            // $attendanceData['userName'] = $userInfo->getName();
            // $attendanceData['userImageUrl'] = $userInfo->getImageUrl();
            array_push($dataArray, $attendanceData);
        }

        return new JsonResponse($dataArray, 200, []);
    }

    //UPDATE ATTENDANCES
    //takes: classId, classSessionId
    //body params: <studentId> : <isAttend> - Example: {"1": true, "9": true, "10": true,...}
    #[Route('/api/classroom/{classId}/classSession/{classSessionId}/attendances', name: 'app_attendances_update', methods: ['POST'])]
    public function updateAttendances($classId, $classSessionId, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, ClassroomRepository $classRepo, ClassSessionRepository $classSessionRepo, AttendanceRepository $attendanceRepo, ManagerRegistry $doctrine)
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        if ($role != 'teacher') {
            return new JsonResponse(['msg' => 'unauthorized'], 401, []);
        }

        $class = $classRepo->findOneBy(['id' => $classId]);
        if ($class == null) {
            return new JsonResponse(['msg' => 'class not found'], 404, []);
        }
        if ($class->getTeacherId() != $userId) {
            return new JsonResponse(['msg' => 'not your class'], 404, []);
        }

        $classSession = $classSessionRepo->findOneBy(['id' => $classSessionId]);
        if ($classSession == null) {
            return new JsonResponse(['msg' => 'class session not found'], 404, []);
        }

        $data = json_decode($request->getContent(), true); //convert data to associative array
        if ($data == null) {
            return new JsonResponse(['msg' => 'data not found'], 400, []);
        }

        $attendances = $attendanceRepo->findBy(['classSessionId' => $classSessionId]);

        $currentAttendancesData = array();
        foreach ($attendances as $attendance) {
            $currentAttendancesData[$attendance->getUserId()] = $attendance->isIsAttend();
        }
        if (
            array_diff_key($currentAttendancesData, $data)
            != array_diff_key($data, $currentAttendancesData)
        ) {
            return new JsonResponse(['msg' => 'data not match'], 400, []);
        }

        $entityManager = $doctrine->getManager();
        foreach ($attendances as $attendance) {
            $studentId = $attendance->getUserId();
            $attendance->setIsAttend($data[$studentId]);
            $attendanceRepo->save($attendance);
        }
        $entityManager->flush();

        return new JsonResponse($attendances, 200, []);
    }

    //GET ATTENDANCE SUMMERIZATION
    //takes: classId
    #[Route(
        '/api/classroom/{classId}/adtendanceSummarization',
        name: 'app_classroom_getSum',
        methods: ['GET']
    )]
    public function getAttendanceSum($classId, Request $request, SessionRepository $sessionRepo, UserRepository $userRepo, ClassroomRepository $classRepo, ClassSessionRepository $classSessionRepo, AttendanceRepository $attendanceRepo)
    {
        $authInfo = getAuthInfo($request, $sessionRepo, $userRepo);
        $userId = $authInfo["userId"];
        $role = $authInfo["role"];

        if ($role != 'teacher') {
            return new JsonResponse(['msg' => 'unauthorized'], 401, []);
        }

        $class = $classRepo->findOneBy(['id' => $classId]);
        if ($class == null) {
            return new JsonResponse(['msg' => 'class not found'], 404, []);
        }
        if ($class->getTeacherId() != $userId) {
            return new JsonResponse(['msg' => 'not your class'], 404, []);
        }

        $percentageDataArray = [];
        $attendanceDataArray = [];
        $classSessions = $classSessionRepo->findBy(['classId' => $classId]);
        foreach ($classSessions as $classSession) {
            $classSessionId = $classSession->getId();
            $attendances = $attendanceRepo->findBy(['classSessionId' => $classSessionId]);
            foreach ($attendances as $attendance) {
                $studentId = $attendance->getUserId();
                $isAttend = $attendance->isIsAttend();

                array_key_exists("{$studentId}", $attendanceDataArray)
                    ? $attendanceDataArray["{$studentId}"] = $isAttend ? $attendanceDataArray["{$studentId}"] + 1 : $attendanceDataArray["{$studentId}"]
                    : $attendanceDataArray["{$studentId}"] = $isAttend ? 1 : 0;
            }
        }
        $numberOfSession = count($attendanceDataArray);
        foreach ($attendanceDataArray as $studentId => $attendedSession) {
            $percentageDataArray["{$studentId}"] = round($attendedSession / $numberOfSession * 100, 2);
        }

        return new JsonResponse($percentageDataArray, 200, []);
    }
}
