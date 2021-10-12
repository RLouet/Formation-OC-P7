<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[UniqueEntity(
    fields: ["name"],
    groups: ["company_create", "company_edit"]
)]
#[UniqueEntity(
    fields: ["email"],
    groups: ["company_create", "company_edit"]
)]
/**
 * @Hateoas\Relation(
 *     "create",
 *     href = @Hateoas\Route(
 *         "app_company_create",
 *         absolute = true
 *     ),
 * )
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *         "app_company_show",
 *         parameters = {"id" = "expr(object.getId())"},
 *         absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"user_details", "users_list", "company_details", "companies_list"})
 * )
 * @Hateoas\Relation(
 *     "users",
 *     href = @Hateoas\Route(
 *         "app_users_list",
 *         parameters = {"company_id" = "expr(object.getId())"},
 *         absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"company_details"})
 * )
 * @Hateoas\Relation(
 *     "users",
 *     embedded = @Hateoas\Embedded("expr(object.getUsers())"),
 *     exclusion = @Hateoas\Exclusion(groups = {"company_details"})
 * )
 */
class Company
{
    use EntityIdManagementTrait;

    #[ORM\Column(type: "string", length: 128, unique: true)]
    #[Serializer\Groups(["user_details", "company_details", "companies_list", "company_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Length(
        min: 1,
        max: 128
    )]
    #[Assert\NotBlank(groups: ["company_create"])]
    private string $name;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    #[Serializer\Groups(["company_details", "companies_list", "company_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Email]
    #[Assert\NotBlank(groups: ["company_create"])]
    private string $email;

    #[ORM\Column(type: "string", length: 32)]
    #[Serializer\Groups(["company_details", "company_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Length(
        min: 6,
        max: 32
    )]
    #[Assert\NotBlank(groups: ["company_create"])]
    private string $phone;

    #[ORM\Column(type: "string", length: 255)]
    #[Serializer\Groups(["company_details", "company_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Length(
        min: 6,
        max: 255
    )]
    #[Assert\NotBlank(groups: ["company_create"])]
    private string $address;

    #[ORM\Column(type: "string")]
    #[Serializer\Groups(["company_details", "company_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Regex(
        pattern: '/^[0-9]{5}$/',
        message: "5 numbers only."
    )]
    #[Assert\NotBlank(groups: ["company_create"])]
    private string $zip;

    #[ORM\Column(type: "string", length: 128)]
    #[Serializer\Groups(["company_details", "company_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Length(
        min: 1,
        max: 128
    )]
    #[Assert\NotBlank(groups: ["company_create"])]
    private string $city;

    #[ORM\Column(type: "string", length: 128)]
    #[Serializer\Groups(["company_details", "companies_list", "company_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Length(
        min: 1,
        max: 128
    )]
    #[Assert\NotBlank(groups: ["company_create"])]
    private string $country;

    #[ORM\Column(type: "datetime")]
    #[Serializer\Groups(["company_details"])]
    #[Serializer\Since("1.0")]
    private \DateTimeInterface $registrationDate;

    #[ORM\OneToMany(
        mappedBy: "company",
        targetEntity: User::class,
        cascade: ["persist", "remove"],
        orphanRemoval: true
    )]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->setRegistrationDate(new \DateTime());
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

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

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setCompany($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCompany() === $this) {
                $user->setCompany(null);
            }
        }

        return $this;
    }
}
