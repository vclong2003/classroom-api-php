<?php

namespace App\Controller;

use App\Entity\ClassSession;
use App\Entity\Attendance;
use App\Repository\AttendanceRepository;
use App\Repository\ClassroomRepository;
use App\Repository\ClassSessionRepository;
use App\Repository\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AttendanceController extends AbstractController
{
    #[Route('/api/classroom/{classId}/attendance/session', name: 'app_attendance', methods: ['GET'])]
    public function getClassSession(Request $request, $classId, AttendanceRepository $attendanceRepo, StudentRepository $studentRepo, ClassroomRepository $classRepo, ClassSessionRepository $classSessionRepo)
    {
        try {
            $class = $classRepo->findOneBy(["id" => $classId]);
            if ($class == null) {
                return new JsonResponse(["msg" => "Class not found"], 400, []);
            } else {
                $classSession = new ClassSession();
                $classSession->setClassId($classId);
                $classSession->setTime(date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . '+ 7 days')));
                $classSessionRepo->save($classSession, true);

                $student = $studentRepo->findOneBy(["classId" => $classId]);
                $studentId = $student->getUserId();
                if ($studentId == null) {
                    return new JsonResponse(["msg" => "No student in this class"], 400, []);
                } else {
                    $attendance = new Attendance();
                    $attendance->setUserId($studentId);
                    $attendance->setClassSessionId($classSession->getId());
                    $attendance->setIsAttend(false);
                    $attendanceRepo->save($attendance, true);
                }
                return new JsonResponse(["sessionId" => $classSession->getId()], 200, []);
            }
        } catch (\Exception $err) {
            return new JsonResponse(["msg" => $err->getMessage()], 400, []);
        }
    }
}