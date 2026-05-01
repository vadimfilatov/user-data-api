<?php

declare(strict_types=1);

namespace App\Document;

use App\Util\UuidGenerator;
use Doctrine\ODM\MongoDB\Mapping\Attribute as ODM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ODM\Document(collection: 'users')]
#[ODM\HasLifecycleCallbacks]
#[ODM\Index(keys: ['lastName' => 'asc'])]
#[ODM\Index(keys: ['identityHash' => 'asc'], unique: true, sparse: true)]
class User
{
    #[ODM\Id(type: 'string', strategy: 'NONE')]
    private string $id;

    #[ODM\Field(type: 'string')]
    private string $firstName;

    #[ODM\Field(type: 'string')]
    private string $lastName;

    #[ODM\Field(type: 'collection')]
    private array $phoneNumbers = [];

    #[ODM\Field(type: 'string')]
    private string $requestIp;

    #[ODM\Field(type: 'string', nullable: true)]
    private ?string $countryCode = null;

    #[ODM\Field(type: 'string', nullable: true)]
    private ?string $countryName = null;

    #[ODM\Field(type: 'string', nullable: true)]
    private ?string $identityHash = null;

    #[ODM\Field(type: 'date_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ODM\Field(type: 'date_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ODM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ODM\PrePersist]
    public function prePersist(): void
    {
        if (!isset($this->id) || $this->id === '') {
            $this->id = UuidGenerator::v7();
        }

        $currentDateTime = new \DateTimeImmutable();
        $this->createdAt = $currentDateTime;
        $this->updatedAt = $currentDateTime;
    }

    #[Groups(['user:list'])]
    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[Groups(['user:list'])]
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = trim($firstName);
        return $this;
    }

    #[Groups(['user:list'])]
    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = trim($lastName);
        return $this;
    }

    #[Groups(['user:list'])]
    public function getPhoneNumbers(): array
    {
        return $this->phoneNumbers;
    }

    public function setPhoneNumbers(array $phoneNumbers): self
    {
        $this->phoneNumbers = $phoneNumbers;
        return $this;
    }

    #[Groups(['user:list'])]
    public function getRequestIp(): string
    {
        return $this->requestIp;
    }

    public function setRequestIp(string $requestIp): self
    {
        $this->requestIp = $requestIp;
        return $this;
    }

    #[Groups(['user:list'])]
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    #[Groups(['user:list'])]
    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function setCountryName(?string $countryName): self
    {
        $this->countryName = $countryName;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getIdentityHash(): ?string
    {
        return $this->identityHash;
    }

    public function setIdentityHash(?string $identityHash): self
    {
        $this->identityHash = $identityHash;
        return $this;
    }
}
