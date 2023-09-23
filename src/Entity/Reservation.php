<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    #[ORM\Column(length: 255)]
    private ?string $Email = null;

    #[ORM\OneToMany(mappedBy: 'reservationId', targetEntity: Gift::class)]
    private Collection $giftId;

    public function __construct()
    {
        $this->giftId = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): static
    {
        $this->Email = $Email;

        return $this;
    }

    /**
     * @return Collection<int, Gift>
     */
    public function getGiftId(): Collection
    {
        return $this->giftId;
    }

    public function addGiftId(Gift $giftId): static
    {
        if (!$this->giftId->contains($giftId)) {
            $this->giftId->add($giftId);
            $giftId->setReservationId($this);
        }

        return $this;
    }

    public function removeGiftId(Gift $giftId): static
    {
        if ($this->giftId->removeElement($giftId)) {
            // set the owning side to null (unless already changed)
            if ($giftId->getReservationId() === $this) {
                $giftId->setReservationId(null);
            }
        }

        return $this;
    }
}
