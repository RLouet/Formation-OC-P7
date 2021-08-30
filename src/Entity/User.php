<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity("email")]
#[UniqueEntity("username")]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    use EntityIdManagementTrait;

    #[ORM\Column(type: "string", length: 128, unique: true)]
    #[Serializer\Groups(["USER_LIST", "USER_CREATE"])]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9]{5,128}$/',
        message: "Between 5 and 128 letters and numbers only."
    )]
    private string $username;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    #[Serializer\Groups(["USER_LIST", "USER_CREATE"])]
    #[Assert\Email]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    #[Serializer\Groups(["USER_CREATE"])]
    private string $password;

    #[ORM\Column(type: "string", length: 255)]
    #[Serializer\Groups(["USER_LIST", "USER_CREATE"])]
    #[Assert\Length(
        min: 2,
        max: 255
    )]
    #[Assert\Regex(
        pattern: '/^[^0-9_!¡?÷?¿\/\\+=@#$%ˆ&*(){}|~<>;:[\]]+$/'
    )]
    private string $lastName;

    #[ORM\Column(type: "string", length: 255)]
    #[Serializer\Groups(["USER_LIST", "USER_CREATE"])]
    #[Assert\Length(
        min: 2,
        max: 255
    )]
    #[Assert\Regex(
        pattern: '/^[^0-9_!¡?÷?¿\/\\+=@#$%ˆ&*(){}|~<>;:[\]]+$/'
    )]
    private string $firstName;

    #[ORM\Column(type: "datetime")]
    #[Serializer\Groups(["USER_DETAILS"])]
    private \DateTimeInterface $registrationDate;

    #[ORM\Column(type: "json")]
    #[Serializer\Groups(["USER_LIST"])]
    private array $roles = [];

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: "users")]
    #[ORM\JoinColumn(nullable: false)]
    #[Serializer\Groups(["USER_DETAILS"])]
    private Company $company;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTimeInterface $registrationDate): self
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }
}
