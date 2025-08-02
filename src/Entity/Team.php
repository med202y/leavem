<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'manager')]
    private ?User $manager = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): static
    {
        $this->manager = $manager;

        return $this;
    }
    /** @return Collection<int, User> */
    public function getUsers(): Collection { return $this->users; }
    public function addUser(User $user): static {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setTeam($this);
        }
        return $this;
    }
    public function removeUser(User $user): static {
        if ($this->users->removeElement($user)) {
            if ($user->getTeam() === $this) $user->setTeam(null);
        }
        return $this;
    }
}
