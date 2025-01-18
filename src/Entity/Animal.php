<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
class Animal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateDernierRepas = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nourritureDernierRepas = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantiteDernierRepas = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'animals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Race $race = null;

    #[ORM\ManyToOne(inversedBy: 'animals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Habitat $habitat = null;

    /**
     * @var Collection<int, ImageAnimal>
     */
    #[ORM\OneToMany(targetEntity: ImageAnimal::class, mappedBy: 'animal', orphanRemoval: true)]
    private Collection $imageAnimals;

    /**
     * @var Collection<int, RapportVeterinaire>
     */
    #[ORM\OneToMany(targetEntity: RapportVeterinaire::class, mappedBy: 'animal', orphanRemoval: true)]
    private Collection $rapportVeterinaires;

    public function __construct()
    {
        $this->imageAnimals = new ArrayCollection();
        $this->rapportVeterinaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getDateDernierRepas(): ?\DateTimeImmutable
    {
        return $this->dateDernierRepas;
    }

    public function setDateDernierRepas(?\DateTimeImmutable $dateDernierRepas): static
    {
        $this->dateDernierRepas = $dateDernierRepas;

        return $this;
    }

    public function getNourritureDernierRepas(): ?string
    {
        return $this->nourritureDernierRepas;
    }

    public function setNourritureDernierRepas(?string $nourritureDernierRepas): static
    {
        $this->nourritureDernierRepas = $nourritureDernierRepas;

        return $this;
    }

    public function getQuantiteDernierRepas(): ?float
    {
        return $this->quantiteDernierRepas;
    }

    public function setQuantiteDernierRepas(?float $quantiteDernierRepas): static
    {
        $this->quantiteDernierRepas = $quantiteDernierRepas;

        return $this;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getRace(): ?Race
    {
        return $this->race;
    }

    public function setRace(?Race $race): static
    {
        $this->race = $race;

        return $this;
    }

    public function getHabitat(): ?Habitat
    {
        return $this->habitat;
    }

    public function setHabitat(?Habitat $habitat): static
    {
        $this->habitat = $habitat;

        return $this;
    }

    /**
     * @return Collection<int, ImageAnimal>
     */
    public function getImageAnimals(): Collection
    {
        return $this->imageAnimals;
    }

    public function addImageAnimal(ImageAnimal $imageAnimal): static
    {
        if (!$this->imageAnimals->contains($imageAnimal)) {
            $this->imageAnimals->add($imageAnimal);
            $imageAnimal->setAnimal($this);
        }

        return $this;
    }

    public function removeImageAnimal(ImageAnimal $imageAnimal): static
    {
        if ($this->imageAnimals->removeElement($imageAnimal)) {
            // set the owning side to null (unless already changed)
            if ($imageAnimal->getAnimal() === $this) {
                $imageAnimal->setAnimal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RapportVeterinaire>
     */
    public function getRapportVeterinaires(): Collection
    {
        return $this->rapportVeterinaires;
    }

    public function addRapportVeterinaire(RapportVeterinaire $rapportVeterinaire): static
    {
        if (!$this->rapportVeterinaires->contains($rapportVeterinaire)) {
            $this->rapportVeterinaires->add($rapportVeterinaire);
            $rapportVeterinaire->setAnimal($this);
        }

        return $this;
    }

    public function removeRapportVeterinaire(RapportVeterinaire $rapportVeterinaire): static
    {
        if ($this->rapportVeterinaires->removeElement($rapportVeterinaire)) {
            // set the owning side to null (unless already changed)
            if ($rapportVeterinaire->getAnimal() === $this) {
                $rapportVeterinaire->setAnimal(null);
            }
        }

        return $this;
    }
}
