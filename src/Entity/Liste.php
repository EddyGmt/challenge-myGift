<?php

namespace App\Entity;

use App\Repository\ListeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListeRepository::class)]
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cover = null;

    #[ORM\Column(length: 50)]
    private ?string $theme = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_ouveture = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_fin_ouverture = null;

    #[ORM\OneToMany(mappedBy: 'liste', targetEntity: Gift::class)]
    private Collection $giftId;

    #[ORM\ManyToOne(inversedBy: 'listeId')]
    private ?User $userId = null;

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

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(?string $cover): self
    {
        $this->cover = $cover;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

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
}
