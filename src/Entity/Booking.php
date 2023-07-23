<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\DateTime]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual('today')]
    private ?\DateTimeImmutable $date = null;

    #[ORM\ManyToOne(inversedBy: 'Booking')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(1)]
    private ?int $nbGuests = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getNbGuests(): ?int
    {
        return $this->nbGuests;
    }

    public function setNbGuests(int $nbGuests): static
    {
        $this->nbGuests = $nbGuests;

        return $this;
    }
}
