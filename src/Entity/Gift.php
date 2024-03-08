<?php

namespace App\Entity;

use App\Repository\GiftRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: GiftRepository::class)]
#[Vich\Uploadable]
class Gift
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'gift', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column]
    private ?float $price = null;

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

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
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
