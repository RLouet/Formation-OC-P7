<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    use EntityIdManagementTrait;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    #[Serializer\Groups(["PRODUCT_LIST"])]
    private string $reference;

    #[ORM\Column(type: "string", length: 255)]
    #[Serializer\Groups(["PRODUCT_LIST"])]
    private string $brand;

    #[ORM\Column(type: "string", length: 255)]
    #[Serializer\Groups(["PRODUCT_LIST"])]
    private string $name;

    #[ORM\Column(type: "string", length: 255)]
    #[Serializer\Groups(["PRODUCT_LIST"])]
    private string $color;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "float")]
    private float $size;

    #[ORM\Column(type: "float")]
    private float $price;

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSize(): ?float
    {
        return $this->size;
    }

    public function setSize(float $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }
}
