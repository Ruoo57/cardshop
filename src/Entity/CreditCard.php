<?php

namespace App\Entity;

use App\Repository\CreditCardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CreditCardRepository::class)]
class CreditCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 16)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 16,
        max: 16,
        exactMessage: 'The credit card number must be exactly {{ limit }} digits.'
    )]
    #[Assert\Regex(
        pattern: '/^\d{16}$/',
        message: 'The credit card number must contain only digits.'
    )]
    private ?string $number = null;

    #[ORM\Column(type: 'string', length: 5)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^(0[1-9]|1[0-2])\/\d{2}$/',
        message: 'The expiration date must be in the format MM/YY.'
    )]
    private ?string $expirationDate = null;

    #[ORM\Column(type: 'string', length: 3)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 3,
        exactMessage: 'The CVV must be exactly {{ limit }} digits.'
    )]
    #[Assert\Regex(
        pattern: '/^\d{3}$/',
        message: 'The CVV must contain only digits.'
    )]
    private ?string $cvv = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'creditCards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;
        return $this;
    }

    public function getExpirationDate(): ?string
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(string $expirationDate): self
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    public function getCvv(): ?string
    {
        return $this->cvv;
    }

    public function setCvv(string $cvv): self
    {
        $this->cvv = $cvv;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
