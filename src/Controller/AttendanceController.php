<?php

namespace App\Controller;

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
    //ADD ATTENDANCES RECORDS
    //takes: classId
    //body params: <studentId> : <isAttend> - Example: {"1": true, "9": true, "10": true,...}
    #[Route('/api/classroom/{classId}/classSession', name: 'app_attendance', methods: ['POST'])]
    public function getClassSession(Request $request)
    {
        $data = json_decode($request->getContent(), true); //convert data to associative array
        foreach ($data as $studentId => $isAttend) {
            echo $studentId .  var_export($isAttend, true) . "\n";
        }
        return new JsonResponse($data, 200, []);
    }
}
