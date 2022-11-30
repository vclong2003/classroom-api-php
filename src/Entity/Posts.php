<?php

namespace App\Entity;

use App\Repository\PostsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity(repositoryClass: PostsRepository::class)]
#[UniqueConstraint(
    fields: ['id', 'classId']
)]
class Posts implements \JsonSerializable
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
    private ?bool $isAssignment = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?int $submitCount = null;

    #[ORM\Column(length: 50)]
    private ?string $dateAdded = null;

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

    public function isIsAssignment(): ?bool
    {
        return $this->isAssignment;
    }

    public function setIsAssignment(bool $isAssignment): self
    {
        $this->isAssignment = $isAssignment;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }


    public function getSubmitCount(): ?int
    {
        return $this->submitCount;
    }

    public function setSubmitCount(int $submitCount): self
    {
        $this->submitCount = $submitCount;

        return $this;
    }

    public function getDateAdded(): ?string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $dateAdded): self
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }
    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "userId" => $this->getUserId(),
            "classId" => $this->getClassId(),
            "isAssignment" => $this->isIsAssignment(),
            "content" => $this->getContent(),
            "submitCount" => $this->getSubmitCount(),
            "dateAdded" => $this->getDateAdded(),
        ];
    }
}
