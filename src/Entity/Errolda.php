<?php

namespace App\Entity;

use App\Repository\ErroldaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ErroldaRepository::class)]
class Errolda
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ident = null;

    #[ORM\Column(length: 2)]
    private ?string $dianac = null;

    #[ORM\Column(length: 2)]
    private ?string $mesnac = null;

    #[ORM\Column(length: 4)]
    private ?string $anonac = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdent(): ?string
    {
        return $this->ident;
    }

    public function setIdent(string $ident): static
    {
        $this->ident = $ident;

        return $this;
    }

    public function getDianac(): ?string
    {
        return $this->dianac;
    }

    public function setDianac(string $dianac): static
    {
        $this->dianac = $dianac;

        return $this;
    }

    public function getMesnac(): ?string
    {
        return $this->mesnac;
    }

    public function setMesnac(string $mesnac): static
    {
        $this->mesnac = $mesnac;

        return $this;
    }

    public function getAnonac(): ?string
    {
        return $this->anonac;
    }

    public function setAnonac(string $anonac): static
    {
        $this->anonac = $anonac;

        return $this;
    }

}
