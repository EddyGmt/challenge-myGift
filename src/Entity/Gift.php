<?php

namespace App\Entity;

use App\Repository\GiftRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GiftRepository::class)]
class Gift
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255)]
    private ?string $link = null;

    #[ORM\ManyToOne(inversedBy: 'giftId')]
    private ?Liste $liste = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isReserved = null;

    #[ORM\ManyToOne(inversedBy: 'giftId')]
    private ?Reservation $reservationId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ReservedBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $EmailReservation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Token = null;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getListe(): ?Liste
    {
        return $this->liste;
    }

    public function setListe(?Liste $liste): static
    {
        $this->liste = $liste;

        return $this;
    }

    public function isIsReserved(): ?bool
    {
        return $this->isReserved;
    }

    public function setIsReserved(?bool $isReserved): static
    {
        $this->isReserved = $isReserved;

        return $this;
    }

    public function getReservationId(): ?Reservation
    {
        return $this->reservationId;
    }

    public function setReservationId(?Reservation $reservationId): static
    {
        $this->reservationId = $reservationId;

        return $this;
    }

    public function getReservedBy(): ?string
    {
        return $this->ReservedBy;
    }

    public function setReservedBy(?string $ReservedBy): static
    {
        $this->ReservedBy = $ReservedBy;

        return $this;
    }

    public function getEmailReservation(): ?string
    {
        return $this->EmailReservation;
    }

    public function setEmailReservation(?string $EmailReservation): static
    {
        $this->EmailReservation = $EmailReservation;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->Token;
    }

    public function setToken(string $Token): static
    {
        $this->Token = $Token;

        return $this;
    }
}
