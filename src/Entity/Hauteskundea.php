<?php

namespace App\Entity;

use App\Repository\HauteskundeaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: HauteskundeaRepository::class)]
class Hauteskundea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $data = null;

    #[ORM\Column(length: 255)]
    #[NotBlank()]
    #[Length(min: 3, max: 255)]
    private ?string $izena_eu = null;

    #[ORM\Column(length: 255)]
    #[NotBlank()]
    #[Length(min: 3, max: 255)]
    private ?string $izena_es = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $oharrak_eu = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $oharrak_es = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $mezua_hautatua_eu = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $mezua_hautatua_es = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $mezua_ez_hautatua_eu = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $mezua_ez_hautatua_es = null;

    /*** ERLAZIOAK ****************************************************************************************************/

    /**
     * @var Collection<int, Herritarra>
     */
    #[ORM\OneToMany(targetEntity: Herritarra::class, mappedBy: 'hauteskundea', cascade: ['remove'])]
    private Collection $herritarrak;

    /*** FUNTZIOAK ****************************************************************************************************/

    public function __construct()
    {
        $this->herritarrak = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->izena_eu;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getIzenaEu(): ?string
    {
        return $this->izena_eu;
    }

    public function setIzenaEu(string $izena_eu): static
    {
        $this->izena_eu = $izena_eu;

        return $this;
    }

    public function getIzenaEs(): ?string
    {
        return $this->izena_es;
    }

    public function setIzenaEs(string $izena_es): static
    {
        $this->izena_es = $izena_es;

        return $this;
    }

    public function getOharrakEu(): ?string
    {
        return $this->oharrak_eu;
    }

    public function setOharrakEu(?string $oharrak_eu): static
    {
        $this->oharrak_eu = $oharrak_eu;

        return $this;
    }

    public function getOharrakEs(): ?string
    {
        return $this->oharrak_es;
    }

    public function setOharrakEs(?string $oharrak_es): static
    {
        $this->oharrak_es = $oharrak_es;

        return $this;
    }

    public function getMezuaHautatuaEu(): ?string
    {
        return $this->mezua_hautatua_eu;
    }

    public function setMezuaHautatuaEu(?string $mezua_hautatua_eu): static
    {
        $this->mezua_hautatua_eu = $mezua_hautatua_eu;

        return $this;
    }

    public function getMezuaHautatuaEs(): ?string
    {
        return $this->mezua_hautatua_es;
    }

    public function setMezuaHautatuaEs(?string $mezua_hautatua_es): static
    {
        $this->mezua_hautatua_es = $mezua_hautatua_es;

        return $this;
    }

    public function getMezuaEzHautatuaEu(): ?string
    {
        return $this->mezua_ez_hautatua_eu;
    }

    public function setMezuaEzHautatuaEu(?string $mezua_ez_hautatua_eu): static
    {
        $this->mezua_ez_hautatua_eu = $mezua_ez_hautatua_eu;

        return $this;
    }

    public function getMezuaEzHautatuaEs(): ?string
    {
        return $this->mezua_ez_hautatua_es;
    }

    public function setMezuaEzHautatuaEs(?string $mezua_ez_hautatua_es): static
    {
        $this->mezua_ez_hautatua_es = $mezua_ez_hautatua_es;

        return $this;
    }

    public function getData(): ?\DateTimeInterface
    {
        return $this->data;
    }

    public function setData(?\DateTimeInterface $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return Collection<int, Herritarra>
     */
    public function getHerritarrak(): Collection
    {
        return $this->herritarrak;
    }

    public function addHerritarrak(Herritarra $herritarrak): static
    {
        if (!$this->herritarrak->contains($herritarrak)) {
            $this->herritarrak->add($herritarrak);
            $herritarrak->setHauteskundea($this);
        }

        return $this;
    }

    public function removeHerritarrak(Herritarra $herritarrak): static
    {
        if ($this->herritarrak->removeElement($herritarrak)) {
            // set the owning side to null (unless already changed)
            if ($herritarrak->getHauteskundea() === $this) {
                $herritarrak->setHauteskundea(null);
            }
        }

        return $this;
    }
}
