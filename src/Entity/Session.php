<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 225)]
    private ?string $sessionId = null;

    #[ORM\Column]
    private ?int $userId = null;

    #[ORM\Column(length: 50)]
    private ?string $expire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): self
    {
        $this->sessionId = $sessionId;

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

    public function getExpire(): ?string
    {
        return $this->expire;
    }

    public function setExpire(string $expire): self
    {
        $this->expire = $expire;

        return $this;
    }
    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "userId" => $this->getUserId(),
            "sessionId" => $this->getSessionId(),
            "expire" => $this->getExpire(),
        ];
    }
}
