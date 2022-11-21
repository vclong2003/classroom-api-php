<?php

namespace App\Entity;

use App\Repository\ClassSessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClassSessionRepository::class)]
class ClassSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $sessionClassId = null;

    #[ORM\Column]
    private ?int $userId = null;

    #[ORM\Column(length: 50)]
    private ?string $time = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSessionClassId(): ?string
    {
        return $this->sessionClassId;
    }

    public function setSessionClassId(string $sessionClassId): self
    {
        $this->sessionClassId = $sessionClassId;

        return $this;
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

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(string $time): self
    {
        $this->time = $time;

        return $this;
    }
}
