<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityIdManagementTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}