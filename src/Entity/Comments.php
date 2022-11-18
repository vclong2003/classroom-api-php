<?php

namespace App\Entity;

use App\Repository\CommentsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentsRepository::class)]
class Comments implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $postId = null;

    #[ORM\Column]
    private ?int $userId = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?int $dateAdded = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): self
    {
        $this->postId = $postId;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDateAdded(): ?int
    {
        return $this->dateAdded;
    }

    public function setDateAdded(int $dateAdded): self
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }
    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "postId" => $this->getPostId(),
            "userId" => $this->getUserId(),
            "content" => $this->getContent(),
            "dateAdded" => $this->getDateAdded()
        ];
    }
}
