<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AttendanceController extends AbstractController
{
    #[Route('/api/test', name: 'app_attendance', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $data = json_decode($request->getContent(), true); //convert data to associative array
        foreach ($data as $studentId => $isAttend) {
            echo $studentId .  var_export($isAttend, true) . "\n";
        }
        return new JsonResponse($data, 200, []);
    }
}
