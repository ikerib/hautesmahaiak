<?php

namespace App\Entity;

use App\Repository\HerritarraRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HerritarraRepository::class)]
class Herritarra
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dist = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $secc = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mesa = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nlocal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nlocalb = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $helbidea = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $barrutia = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cargofinal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $kargua = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cargo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ident = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apellido1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apellido2 = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $eguna = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $hilabetea = null;

    #[ORM\Column(length: 4, nullable: true)]
    private ?string $urtea = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $jaioteguna = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $active = 0;

    /******************************************************************************************************************/
    /*** FUNTZIOAK ****************************************************************************************************/
    /******************************************************************************************************************/

    public function __toString()
    {
        return ' (' . (string)$this->cargofinal . ') ' .(string)$this->nombre . ' ' . (string)$this->apellido1 . ' ' . (string)$this->apellido2;
    }

    /******************************************************************************************************************/
    /*** ERLAZIOAK ****************************************************************************************************/
    /******************************************************************************************************************/

    #[ORM\ManyToOne(inversedBy: 'herritarrak')]
    private ?Hauteskundea $hauteskundea = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDist(): ?string
    {
        return $this->dist;
    }

    public function setDist(?string $dist): static
    {
        $this->dist = $dist;

        return $this;
    }

    public function getSecc(): ?string
    {
        return $this->secc;
    }

    public function setSecc(?string $secc): static
    {
        $this->secc = $secc;

        return $this;
    }

    public function getMesa(): ?string
    {
        return $this->mesa;
    }

    public function setMesa(?string $mesa): static
    {
        $this->mesa = $mesa;

        return $this;
    }

    public function getNlocal(): ?string
    {
        return $this->nlocal;
    }

    public function setNlocal(?string $nlocal): static
    {
        $this->nlocal = $nlocal;

        return $this;
    }

    public function getNlocalb(): ?string
    {
        return $this->nlocalb;
    }

    public function setNlocalb(?string $nlocalb): static
    {
        $this->nlocalb = $nlocalb;

        return $this;
    }

    public function getHelbidea(): ?string
    {
        return $this->helbidea;
    }

    public function setHelbidea(?string $helbidea): static
    {
        $this->helbidea = $helbidea;

        return $this;
    }

    public function getBarrutia(): ?string
    {
        return $this->barrutia;
    }

    public function setBarrutia(?string $barrutia): static
    {
        $this->barrutia = $barrutia;

        return $this;
    }

    public function getCargofinal(): ?string
    {
        return $this->cargofinal;
    }

    public function setCargofinal(?string $cargofinal): static
    {
        $this->cargofinal = $cargofinal;

        return $this;
    }

    public function getKargua(): ?string
    {
        return $this->kargua;
    }

    public function setKargua(?string $kargua): static
    {
        $this->kargua = $kargua;

        return $this;
    }

    public function getCargo(): ?string
    {
        return $this->cargo;
    }

    public function setCargo(?string $cargo): static
    {
        $this->cargo = $cargo;

        return $this;
    }

    public function getIdent(): ?string
    {
        return $this->ident;
    }

    public function setIdent(?string $ident): static
    {
        $this->ident = $ident;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getApellido1(): ?string
    {
        return $this->apellido1;
    }

    public function setApellido1(?string $apellido1): static
    {
        $this->apellido1 = $apellido1;

        return $this;
    }

    public function getApellido2(): ?string
    {
        return $this->apellido2;
    }

    public function setApellido2(?string $apellido2): static
    {
        $this->apellido2 = $apellido2;

        return $this;
    }

    public function getEguna(): ?string
    {
        return $this->eguna;
    }

    public function setEguna(?string $eguna): static
    {
        $this->eguna = $eguna;

        return $this;
    }

    public function getHilabetea(): ?string
    {
        return $this->hilabetea;
    }

    public function setHilabetea(?string $hilabetea): static
    {
        $this->hilabetea = $hilabetea;

        return $this;
    }

    public function getUrtea(): ?string
    {
        return $this->urtea;
    }

    public function setUrtea(?string $urtea): static
    {
        $this->urtea = $urtea;

        return $this;
    }

    public function getJaioteguna(): ?\DateTimeInterface
    {
        return $this->jaioteguna;
    }

    public function setJaioteguna(?\DateTimeInterface $jaioteguna): static
    {
        $this->jaioteguna = $jaioteguna;

        return $this;
    }

    public function getHauteskundea(): ?Hauteskundea
    {
        return $this->hauteskundea;
    }

    public function setHauteskundea(?Hauteskundea $hauteskundea): static
    {
        $this->hauteskundea = $hauteskundea;

        return $this;
    }

    public function getActive(): ?int
    {
        return $this->active;
    }

    public function setActive(int $active): static
    {
        $this->active = $active;

        return $this;
    }

}
