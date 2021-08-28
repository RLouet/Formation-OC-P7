<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

trait EntityIdManagementTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[Serializer\Groups(["PRODUCT_LIST"])]
    private int $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}