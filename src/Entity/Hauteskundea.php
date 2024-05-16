<?php

namespace App\Entity;

use App\Repository\HauteskundeaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: HauteskundeaRepository::class)]
class Hauteskundea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[NotBlank()]
    #[Length(min: 3, max: 255)]
    private ?string $izena = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $oharrak = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $data = null;

    /******************************************************************************************************************/
    /*** ERLAZIOAK ****************************************************************************************************/
    /******************************************************************************************************************/

    /**
     * @var Collection<int, Herritarra>
     */
    #[ORM\OneToMany(targetEntity: Herritarra::class, mappedBy: 'hauteskundea', cascade: ['remove'])]
    private Collection $herritarrak;

    #[ORM\Column]
    private ?bool $active = null;

    /******************************************************************************************************************/
    /*** FUNTZIOAK ****************************************************************************************************/
    /******************************************************************************************************************/

    public function __construct()
    {
        $this->herritarrak = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string)$this->izena;
    }

    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /******************************************************************************************************************/

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIzena(): ?string
    {
        return $this->izena;
    }

    public function setIzena(string $izena): static
    {
        $this->izena = $izena;

        return $this;
    }

    public function getOharrak(): ?string
    {
        return $this->oharrak;
    }

    public function setOharrak(?string $oharrak): static
    {
        $this->oharrak = $oharrak;

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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }


}
