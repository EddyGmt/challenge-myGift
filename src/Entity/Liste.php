<?php

namespace App\Entity;

use App\Repository\ListeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ListeRepository::class)]
#[Vich\Uploadable]
class Liste
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $theme = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $password = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'liste', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_ouveture = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_fin_ouverture = null;

    #[ORM\OneToMany(mappedBy: 'liste', targetEntity: Gift::class)]
    private Collection $giftId;

    #[ORM\ManyToOne(inversedBy: 'listeId')]
    private ?User $userId = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isArchived = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isPrivate = null;

    public function __construct()
    {
        $this->giftId = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
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

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function getDateOuveture(): ?\DateTimeInterface
    {
        return $this->date_ouveture;
    }

    public function setDateOuveture(\DateTimeInterface $date_ouveture): self
    {
        $this->date_ouveture = $date_ouveture;

        return $this;
    }

    public function getDateFinOuverture(): ?\DateTimeInterface
    {
        return $this->date_fin_ouverture;
    }

    public function setDateFinOuverture(?\DateTimeInterface $date_fin_ouverture): self
    {
        $this->date_fin_ouverture = $date_fin_ouverture;

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
            $giftId->setListe($this);
        }

        return $this;
    }

    public function removeGiftId(Gift $giftId): static
    {
        if ($this->giftId->removeElement($giftId)) {
            // set the owning side to null (unless already changed)
            if ($giftId->getListe() === $this) {
                $giftId->setListe(null);
            }
        }

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function isIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(?bool $isArchived): static
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    public function isIsPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(?bool $isPrivate): static
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }
}
