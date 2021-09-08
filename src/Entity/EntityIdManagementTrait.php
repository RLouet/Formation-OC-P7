<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

trait EntityIdManagementTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[Serializer\Groups(["products_list", "user_list"])]
    #[Serializer\Since("1.0")]
    private int $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}