<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;
use Hateoas\Configuration\Annotation as Hateoas;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(
    fields: "email",
    groups: ["user_create", "user_edit"]
)]
#[UniqueEntity(
    fields: "username",
    groups: ["user_create", "user_edit"]
)]
/**
 * @Hateoas\Relation(
 *     "create",
 *     href = @Hateoas\Route(
 *         "app_user_create",
 *         parameters = {"company_id" = "expr(object.getCompany().getId())"},
 *         absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"user_details"})
 * )
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *         "app_user_show",
 *         parameters = {"company_id" = "expr(object.getCompany().getId())", "user_id" = "expr(object.getId())"},
 *         absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"user_details", "users_list"})
 * )
 * @Hateoas\Relation(
 *     "update",
 *     href = @Hateoas\Route(
 *         "app_user_update",
 *         parameters = {"company_id" = "expr(object.getCompany().getId())", "user_id" = "expr(object.getId())"},
 *         absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"user_details"})
 * )
 * @Hateoas\Relation(
 *     "delete",
 *     href = @Hateoas\Route(
 *         "app_user_delete",
 *         parameters = {"company_id" = "expr(object.getCompany().getId())", "user_id" = "expr(object.getId())"},
 *         absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"user_details"})
 * )
 * @Hateoas\Relation(
 *     "company",
 *     href = @Hateoas\Route(
 *         "app_company_show",
 *         parameters = {"id" = "expr(object.getCompany().getId())"},
 *         absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"user_details"})
 * )
 */
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    use EntityIdManagementTrait;

    #[ORM\Column(type: "string", length: 128, unique: true)]
    #[Serializer\Groups(["users_list", "user_create", "user_details", "user_login"])]
    #[Serializer\Since("1.0")]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9]{5,128}$/',
        message: "Between 5 and 128 letters and numbers only."
    )]
    #[Assert\NotBlank(groups: ["user_create"])]
    /**
     * @OA\Property(default="JeanBon")
     */
    private string $username;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    #[Serializer\Groups(["users_list", "user_create", "user_details"])]
    #[Serializer\Since("1.0")]
    #[Assert\Email]
    #[Assert\NotBlank(groups: ["user_create"])]
    /**
     * @OA\Property(default="jeanbon@example.com")
     */
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    #[Serializer\Groups(["users_list", "user_create", "user_details"])]
    #[Serializer\Since("1.0")]
    #[Assert\Length(
        min: 2,
        max: 255
    )]
    #[Assert\Regex(
        pattern: '/^[^0-9_!????????\/\\+=@#$%??&*(){}|~<>;:[\]]+$/'
    )]
    #[Assert\NotBlank(groups: ["user_create"])]
    /**
     * @OA\Property(default="Jean")
     */
    private string $lastName;

    #[ORM\Column(type: "string", length: 255)]
    #[Serializer\Groups(["users_list", "user_create", "user_details"])]
    #[Serializer\Since("1.0")]
    #[Assert\Length(
        min: 2,
        max: 255
    )]
    #[Assert\Regex(
        pattern: '/^[^0-9_!????????\/\\+=@#$%??&*(){}|~<>;:[\]]+$/'
    )]
    #[Assert\NotBlank(groups: ["user_create"])]
    /**
     * @OA\Property(default="Bon")
     */
    private string $firstName;

    #[ORM\Column(type: "string", length: 255)]
    #[Serializer\Groups(["user_create", "user_login"])]
    #[Serializer\Since("1.0")]
    #[Assert\Length(
        min: 8,
        max: 128
    )]
    #[Assert\NotBlank(groups: ["user_create"])]
    /**
     * @OA\Property(default="P@ssword!")
     */
    private string $password;

    #[ORM\Column(type: "datetime")]
    #[Serializer\Groups(["user_details"])]
    #[Serializer\Since("1.0")]
    private \DateTimeInterface $registrationDate;

    #[ORM\Column(type: "json")]
    #[Serializer\Groups(["users_list", "user_details"])]
    #[Serializer\Since("1.0")]
    /**
     * @OA\Property(
     *     type="array",
     *     @OA\Items(
     *         type="string",
     *         default="ROLE_USER"
     *     )
     * )
     */
    private array $roles = [];

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: "users")]
    #[ORM\JoinColumn(nullable: false)]
    private Company $company;

    public function getUsername(): ?string
    {
        return $this->username ?? null;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email ?? null;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->username;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password ?? null;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName ?? null;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName ?? null;
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

    public function update(User $user, UserPasswordHasherInterface $passwordHasher): self
    {
        if ($user->getUsername()) {
            $this->username = $user->getUsername();
        }
        if ($user->getEmail()) {
            $this->email = $user->getEmail();
        }
        if ($user->getLastName()) {
            $this->lastName = $user->getLastName();
        }
        if ($user->getFirstName()) {
            $this->firstName = $user->getFirstName();
        }
        if ($user->getPassword()) {
            $this->password = $passwordHasher->hashPassword($user, $user->getPassword());
        }
        return $this;
    }
}
