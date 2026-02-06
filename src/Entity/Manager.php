<?php

namespace App\Entity;

use App\Enum\ValidationStrategy;
use App\Repository\ManagerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ManagerRepository::class)]
class Manager extends User
{

    #[ORM\GeneratedValue]


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $equipe = null;

    #[ORM\Column(enumType: ValidationStrategy::class)]
    private ?ValidationStrategy $Typedevalidation = null;



    public function getEquipe(): ?string
    {
        return $this->equipe;
    }

    public function setEquipe(?string $equipe): static
    {
        $this->equipe = $equipe;

        return $this;
    }

    public function getTypedevalidation(): ?ValidationStrategy
    {
        return $this->Typedevalidation;
    }

    public function setTypedevalidation(ValidationStrategy $Typedevalidation): static
    {
        $this->Typedevalidation = $Typedevalidation;

        return $this;
    }
}
