<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;
use Hateoas\Configuration\Annotation as Hateoas;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[UniqueEntity(
    fields: ["reference"],
    groups: ["product_create", "product_edit"]
)]
/**
 * @Hateoas\Relation(
 *     "create",
 *     href = @Hateoas\Route(
 *         "app_product_create",
 *         absolute = true
 *     ),
 * )
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *         "app_product_show",
 *         parameters = {"id" = "expr(object.getId())"},
 *         absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"products_list"})
 * )
 * @Hateoas\Relation(
 *     "update",
 *     href = @Hateoas\Route(
 *         "app_product_update",
 *         parameters = {"id" = "expr(object.getId())"},
 *         absolute = true
 *     ),
 * )
 * @Hateoas\Relation(
 *     "delete",
 *     href = @Hateoas\Route(
 *         "app_product_delete",
 *         parameters = {"id" = "expr(object.getId())"},
 *         absolute = true
 *     ),
 * )
 */
class Product
{
    use EntityIdManagementTrait;

    #[ORM\Column(type: "string", length: 32, unique: true)]
    #[Serializer\Groups(["products_list", "product_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9-]{5,32}$/',
        message: "Between 5 and 32 letters, numbers and - only."
    )]
    #[Assert\NotBlank(groups: ["product_create"])]
    /**
     * @OA\Property(default="a1b2c3d4")
     */
    private string $reference;

    #[ORM\Column(type: "string", length: 128)]
    #[Serializer\Groups(["products_list", "product_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Length(
        min: 1,
        max: 128
    )]
    #[Assert\NotBlank(groups: ["product_create"])]
    /**
     * @OA\Property(default="Samsung")
     */
    private string $brand;

    #[ORM\Column(type: "string", length: 128)]
    #[Serializer\Groups(["products_list", "product_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Length(
        min: 1,
        max: 128
    )]
    #[Assert\NotBlank(groups: ["product_create"])]
    /**
     * @OA\Property(default="Galaxy S123")
     */
    private string $name;

    #[ORM\Column(type: "string", length: 128)]
    #[Serializer\Groups(["products_list", "product_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Length(
        min: 1,
        max: 128
    )]
    #[Assert\NotBlank(groups: ["product_create"])]
    /**
     * @OA\Property(default="Pink")
     */
    private string $color;

    #[ORM\Column(type: "text")]
    #[Serializer\Groups(["product_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Length(
        min: 10,
        max: 10240
    )]
    #[Assert\NotBlank(groups: ["product_create"])]
    /**
     * @OA\Property(default="This is a great phone !!")
     */
    private string $description;

    #[ORM\Column(type: "float")]
    #[Serializer\Groups(["product_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Type(
        type: 'float',
        message: 'float required.'
    )]
    #[Assert\NotBlank(groups: ["product_create"])]
    /**
     * @OA\Property(default="8.9")
     */
    private float $size;

    #[ORM\Column(type: "float")]
    #[Serializer\Groups(["product_create"])]
    #[Serializer\Since("1.0")]
    #[Assert\Type(
        type: 'float',
        message: 'float required.'
    )]
    #[Assert\NotBlank(groups: ["product_create"])]
    /**
     * @OA\Property(default="1234.99")
     */
    private float $price;

    public function getReference(): ?string
    {
        return $this->reference ?? null;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand ?? null;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
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

    public function getColor(): ?string
    {
        return $this->color ?? null;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description ?? null;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSize(): ?float
    {
        return $this->size ?? null;
    }

    public function setSize(float $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price ?? null;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function update(Product $product): self
    {
        if ($product->getBrand()) {
            $this->brand = $product->getBrand();
        }
        if ($product->getName()) {
            $this->name = $product->getName();
        }
        if ($product->getReference()) {
            $this->reference = $product->getReference();
        }
        if ($product->getColor()) {
            $this->color = $product->getColor();
        }
        if ($product->getDescription()) {
            $this->description = $product->getDescription();
        }
        if ($product->getSize()) {
            $this->size = $product->getSize();
        }
        if ($product->getPrice()) {
            $this->price = $product->getPrice();
        }
        return $this;
    }
}
