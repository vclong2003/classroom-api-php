<?php

namespace App\Entity;

use App\Repository\AttendanceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttendanceRepository::class)]
class Attendance implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $userId = null;

    #[ORM\Column]
    private ?int $classId = null;

    #[ORM\Column]
    private ?bool $isAttend = null;

    #[ORM\Column]
    private ?int $classSessionId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getClassId(): ?int
    {
        return $this->classId;
    }

    public function setClassId(int $classId): self
    {
        $this->classId = $classId;

        return $this;
    }

    public function isIsAttend(): ?bool
    {
        return $this->isAttend;
    }

    public function setIsAttend(bool $isAttend): self
    {
        $this->isAttend = $isAttend;

        return $this;
    }

    public function getClassSessionId(): ?int
    {
        return $this->classSessionId;
    }

    public function setClassSessionId(int $classSessionId): self
    {
        $this->classSessionId = $classSessionId;

        return $this;
    }
    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "userId" => $this->getUserId(),
            "classId" => $this->getClassId(),
            "classSessionId" => $this->getClassSessionId(),
            "isAttend" => $this->isIsAttend()
        ];
    }
}
