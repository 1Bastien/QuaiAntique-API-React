<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getCustomer', 'getBooking'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual('today')]
    #[Groups(['getCustomer', 'getBooking'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'Booking')]
    private ?Customer $customer = null;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(1)]
    #[Groups(['getCustomer', 'getBooking'])]
    private ?int $nbGuests = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['getCustomer', 'getBooking'])]
    private ?string $email = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Groups(['getCustomer', 'getBooking'])]
    private ?string $nameOfBooking = null;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getNameOfBooking(): ?string
    {
        return $this->nameOfBooking;
    }

    public function setNameOfBooking(string $nameOfBooking): static
    {
        $this->nameOfBooking = $nameOfBooking;

        return $this;
    }
}
