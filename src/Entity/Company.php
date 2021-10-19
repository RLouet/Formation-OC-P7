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
 *     exclusion = @Hateoas\Exclusion(groups = {"company_details"})
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
 *     "update",
 *     href = @Hateoas\Route(
 *         "app_company_update",
 *         parameters = {"id" = "expr(object.getId())"},
 *         absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"company_details", "company_create"})
 * )
 * @Hateoas\Relation(
 *     "delete",
 *     href = @Hateoas\Route(
 *         "app_company_delete",
 *         parameters = {"id" = "expr(object.getId())"},
 *         absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"company_details", "company_create"})
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
        $this->init();
    }

    public function init()
    {
        $this->users = new ArrayCollection();
        $this->setRegistrationDate(new \DateTime());
    }

    public function getName(): ?string
    {
        return $this->name ?? null;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email ?? null;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone ?? null;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address ?? null;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip ?? null;
    }

    public function setZip(string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city ?? null;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country ?? null;
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

    public function update(Company $company): self
    {
        if ($company->getName()) {
            $this->name = $company->getName();
        }
        if ($company->getEmail()) {
            $this->email = $company->getEmail();
        }
        if ($company->getPhone()) {
            $this->phone = $company->getPhone();
        }
        if ($company->getAddress()) {
            $this->address = $company->getAddress();
        }
        if ($company->getZip()) {
            $this->zip = $company->getZip();
        }
        if ($company->getCity()) {
            $this->city = $company->getCity();
        }
        if ($company->getCountry()) {
            $this->country = $company->getCountry();
        }
        return $this;
    }
}
