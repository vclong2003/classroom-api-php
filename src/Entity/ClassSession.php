<?php

namespace App\Entity;

use App\Repository\ClassSessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClassSessionRepository::class)]
class ClassSession implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $time = null;

    #[ORM\Column]
    private ?int $classId = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getClassId(): ?int
    {
        return $this->classId;
    }

    public function setClassId(int $classId): self
    {
        $this->classId = $classId;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "classId" => $this->getClassId(),
            "time" => $this->getTime()
        ];
    }
}
