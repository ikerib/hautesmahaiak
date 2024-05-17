<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    final public const ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 15)]
    private ?string $NA = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $izena = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $abizena = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $displayname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $saila = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lanpostua = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailfrom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailto = null;

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function __construct()
    {
    }

    public function __toString()
    {
        return (string) $this->displayname;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNA(): ?string
    {
        return $this->NA;
    }

    public function setNA(string $NA): static
    {
        $this->NA = $NA;

        return $this;
    }

    public function getIzena(): ?string
    {
        return $this->izena;
    }

    public function setIzena(?string $izena): static
    {
        $this->izena = $izena;

        return $this;
    }

    public function getDisplayname(): ?string
    {
        return $this->displayname;
    }

    public function setDisplayname(?string $displayname): static
    {
        $this->displayname = $displayname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getSaila(): ?string
    {
        return $this->saila;
    }

    public function setSaila(?string $saila): static
    {
        $this->saila = $saila;

        return $this;
    }

    public function getLanpostua(): ?string
    {
        return $this->lanpostua;
    }

    public function setLanpostua(?string $lanpostua): static
    {
        $this->lanpostua = $lanpostua;

        return $this;
    }

    public function getAbizena(): ?string
    {
        return $this->abizena;
    }

    public function setAbizena(?string $abizena): static
    {
        $this->abizena = $abizena;

        return $this;
    }

    public function getMailfrom(): ?string
    {
        return $this->mailfrom;
    }

    public function setMailfrom(?string $mailfrom): static
    {
        $this->mailfrom = $mailfrom;

        return $this;
    }

    public function getMailto(): ?string
    {
        return $this->mailto;
    }

    public function setMailto(?string $mailto): static
    {
        $this->mailto = $mailto;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
