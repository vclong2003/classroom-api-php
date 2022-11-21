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
        $data = json_decode($request->getContent(), true); //convert data to associative array
        foreach ($data as $studentId => $isAttend) {
            echo $studentId .  var_export($isAttend, true) . "\n";
        }
        return new JsonResponse($data, 200, []);
    }
}