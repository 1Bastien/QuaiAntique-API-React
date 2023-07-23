<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
class Restaurant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank('Le nom du restaurant est obligatoire')]
    #[Assert\Length(min: 1, max: 30, minMessage: "Le nom doit faire au moins {{ limit }} caractères", maxMessage: "Le nom ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotNull('La capacité du restaurant est obligatoire')]
    #[Assert\GreaterThanOrEqual(1)]
    private ?int $seatingCapacity = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank("L'adresse du restaurant est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le nom doit faire au moins {{ limit }} caractères", maxMessage: "Le nom ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $address = null;

    #[ORM\Column]
    private array $openingTimes = [];

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?bool $activated = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSeatingCapacity(): ?int
    {
        return $this->seatingCapacity;
    }

    public function setSeatingCapacity(int $seatingCapacity): static
    {
        $this->seatingCapacity = $seatingCapacity;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getOpeningTimes(): array
    {
        return $this->openingTimes;
    }

    public function setOpeningTimes(array $openingTimes): static
    {
        $this->openingTimes = $openingTimes;

        return $this;
    }

    public function isActivated(): ?bool
    {
        return $this->activated;
    }

    public function setActivated(bool $activated): static
    {
        $this->activated = $activated;

        return $this;
    }
}
