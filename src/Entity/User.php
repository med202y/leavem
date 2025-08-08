<?php

namespace App\Entity;

use App\Enum\Role;
use App\Enum\Departement;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'user_type', type: 'string')]
#[ORM\DiscriminatorMap(['user' => User::class, 'manager' => Manager::class])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, enumType: Role::class)]
    private array $roles = [];

    #[ORM\Column]
    private ?float $leaveBalance = null;

    #[ORM\Column(enumType: Departement::class)]
    private ?Departement $department = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'recipient')]
    private Collection $notifications;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Team $team = null;
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'direct_manager_id', nullable: true)]
    private ?User $directManager = null;

    /**
     * @var Collection<int, Leave>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Leave::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $leaves;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $hireDate = null;

    /**
     * @var Collection<int, Team>
     */
    #[ORM\OneToMany(targetEntity: Team::class, mappedBy: 'manager')]
    private Collection $managedTeams;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->leaves = new ArrayCollection();
        $this->managedTeams = new ArrayCollection();
    }
        public function getManager(): ?User
    {
        // First check direct manager, then fall back to team manager
        return $this->directManager ?? $this->team?->getManager();
    }

    public function setManager(?User $manager): self
    {
        $this->directManager = $manager;
        return $this;
    }
    /**
     * @return Collection<int, Leave>
     */
    public function getLeaves(): Collection
    {
        return $this->leaves;
    }

    public function addLeave(Leave $leave): self
    {
        if (!$this->leaves->contains($leave)) {
            $this->leaves->add($leave);
            $leave->setUser($this);
        }
        return $this;
    }

    public function removeLeave(Leave $leave): self
    {
        if ($this->leaves->removeElement($leave)) {
            if ($leave->getUser() === $this) {
                $leave->setUser(null);
            }
        }
        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;
        return $this;
    }

   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return Role[]
     */
    public function getRolesArray(): array
    {
        return $this->roles;
    }

    public function setRolesArray(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getLeaveBalance(): ?float
    {
        return $this->leaveBalance;
    }

    public function setLeaveBalance(float $leaveBalance): static
    {
        $this->leaveBalance = $leaveBalance;
        return $this;
    }

    public function getDepartment(): ?Departement
    {
        return $this->department;
    }

    public function setDepartment(Departement $department): static
    {
        $this->department = $department;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        // Convert Role enums to string values
        return array_map(fn($role) => $role->value, $this->roles);
    }

    public function eraseCredentials(): void
    {
        // Clean up sensitive data if needed
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setRecipient($this);
        }
        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getRecipient() === $this) {
                $notification->setRecipient(null);
            }
        }
        return $this;
    }

    public function getHireDate(): ?\DateTimeInterface
    {
        return $this->hireDate;
    }

    public function setHireDate(?\DateTimeInterface $hireDate): self
    {
        $this->hireDate = $hireDate;
        return $this;
    }

    public function addManagedTeam(Team $team): static
    {
        if (!$this->managedTeams->contains($team)) {
            $this->managedTeams->add($team);
            $team->setManager($this);
        }
        return $this;
    }

    public function removeManagedTeam(Team $team): static
    {
        if ($this->managedTeams->removeElement($team)) {
            if ($team->getManager() === $this) {
                $team->setManager(null);
            }
        }
        return $this;
    }
}
