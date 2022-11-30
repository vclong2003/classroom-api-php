<?php

namespace App\Entity;

use App\Repository\ClassroomRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity(repositoryClass: ClassroomRepository::class)]
#[UniqueConstraint(
    fields: ['id', 'teacherId']
)]
class Classroom implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $teacherId = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $startDate = null;

    #[ORM\Column]
    private ?int $studentCount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeacherId(): ?int
    {
        return $this->teacherId;
    }

    public function setTeacherId(int $teacherId): self
    {
        $this->teacherId = $teacherId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setStartDate(string $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getStudentCount(): ?int
    {
        return $this->studentCount;
    }

    public function setStudentCount(int $studentCount): self
    {
        $this->studentCount = $studentCount;

        return $this;
    }
    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "teacherId" => $this->getTeacherId(),
            "name" => $this->getName(),
            "startDate" => $this->getStartDate(),
            "studentCount" => $this->getStudentCount()
        ];
    }
}
