<?php

namespace App\Entity;

use App\Repository\RapportVeterinaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RapportVeterinaireRepository::class)]
class RapportVeterinaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $etatAnimal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nourriturePropose = null;

    #[ORM\Column(nullable: true)]
    private ?int $grammagePropose = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $detailAnimal = null;

    #[ORM\ManyToOne(inversedBy: 'rapportVeterinaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'rapportVeterinaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Animal $animal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEtatAnimal(): ?string
    {
        return $this->etatAnimal;
    }

    public function setEtatAnimal(string $etatAnimal): static
    {
        $this->etatAnimal = $etatAnimal;

        return $this;
    }

    public function getNourriturePropose(): ?string
    {
        return $this->nourriturePropose;
    }

    public function setNourriturePropose(?string $nourriturePropose): static
    {
        $this->nourriturePropose = $nourriturePropose;

        return $this;
    }

    public function getGrammagePropose(): ?int
    {
        return $this->grammagePropose;
    }

    public function setGrammagePropose(?int $grammagePropose): static
    {
        $this->grammagePropose = $grammagePropose;

        return $this;
    }

    public function getDetailAnimal(): ?string
    {
        return $this->detailAnimal;
    }

    public function setDetailAnimal(?string $detailAnimal): static
    {
        $this->detailAnimal = $detailAnimal;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAnimal(): ?Animal
    {
        return $this->animal;
    }

    public function setAnimal(?Animal $animal): static
    {
        $this->animal = $animal;

        return $this;
    }
}
