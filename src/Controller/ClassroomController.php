<?php

namespace App\Controller;

use App\Entity\Classroom;
use App\Repository\ClassroomRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClassroomController extends AbstractController
{
    #[Route('/api/classroom', name: 'app_classroom_create', methods: ['POST'])]
    public function addClassroom(UserRepository $userRepo, ClassroomRepository $classroomRepo, Request $request, SessionRepository $sessionRepo): Response
    {
        $data = json_decode($request->getContent(), true); //convert data to associative array
        $userId = findUserId($request, $sessionRepo);
        $role = $userRepo->findOneBy(["userId" => $userId])->getRole();

        if ($role == "teacher") {
            $classroom = new Classroom();
            $classroom->setTeacherId($userId);
            $classroom->setName($data['name']);
            $classroom->setStartDate(time());
            $classroom->setStudentCount(0);

            return new JsonResponse(["msg" => "Created"], 201, []);
        }
    }

    #[Route('/api/classroom', name: 'app_classroom_get', methods: ['GET'])]
    public function getClassroom(UserRepository $userRepo, ClassroomRepository $classroomRepo, Request $request, SessionRepository $sessionRepo): Response
    {
        $data = json_decode($request->getContent(), true); //convert data to associative array
        $userId = findUserId($request, $sessionRepo);
        $role = $userRepo->findOneBy(["userId" => $userId])->getRole();

        if ($role == "teacher") {
            $classroom = new Classroom();
            $classroom->setTeacherId($userId);
            $classroom->setName($data['name']);
            $classroom->setStartDate(time());
            $classroom->setStudentCount(0);

            return new JsonResponse(["msg" => "Created"], 201, []);
        }
    }
}
