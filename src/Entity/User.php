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
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $mot_de_passe = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, enumType: Role::class)]
    private array $Role = [];

    #[ORM\Column]
    private ?float $solde_conge = null;

    #[ORM\Column(enumType: Departement::class)]
    private ?Departement $departement = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Observations = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $chemin = null;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'recipient')]
    private Collection $notifications;


    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Team $team = null;
    /**
     * @var Collection<int, Conge>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Conge::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $conges;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->conges       = new ArrayCollection();
    }

    /**
     * @return Collection<int, Conge>
     */
    public function getConges(): Collection
    {
        return $this->conges;
    }

    public function addConge(Conge $conge): self
    {
        if (!$this->conges->contains($conge)) {
            $this->conges->add($conge);
            $conge->setUser($this);
        }

        return $this;
    }

    public function removeConge(Conge $conge): self
    {
        if ($this->conges->removeElement($conge)) {
            // détache l'utilisateur si besoin
            if ($conge->getUser() === $this) {
                $conge->setUser(null);
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

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

    public function getMotDePasse(): ?string
    {
        return $this->mot_de_passe;
    }

    public function setMotDePasse(string $mot_de_passe): static
    {
        $this->mot_de_passe = $mot_de_passe;

        return $this;
    }

    /**
     * @return Role[]
     */
    public function getRole(): array
    {
        return $this->Role;
    }

    public function setRole(array $Role): static
    {
        $this->Role = $Role;

        return $this;
    }

    public function getSoldeConge(): ?float
    {
        return $this->solde_conge;
    }

    public function setSoldeConge(float $solde_conge): static
    {
        $this->solde_conge = $solde_conge;

        return $this;
    }

    public function getDepartement(): ?departement
    {
        return $this->departement;
    }

    public function setDepartement(departement $departement): static
    {
        $this->departement = $departement;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->mot_de_passe;
    }

    public function getRoles(): array
    {
        // Convertir les objets Role en string, si tu utilises un Enum
        return array_map(fn($role) => $role->value, $this->Role);
    }

    public function eraseCredentials(): void
    {
        // S'il y a des données sensibles temporaires, les nettoyer ici (ex: mot de passe en clair)
    }

    public function getCIN(): ?string
    {
        return $this->CIN;
    }

    public function setCIN(string $CIN): static
    {
        $this->CIN = $CIN;

        return $this;
    }
    public function getObservations(): ?string
    {
        return $this->Observations;
    }

    public function setObservations(?string $Observations): static
    {
        $this->Observations = $Observations;

        return $this;
    }

    public function getChemin(): ?string
    {
        return $this->chemin;
    }

    public function setChemin(?string $chemin): static
    {
        $this->chemin = $chemin;

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
            // set the owning side to null (unless already changed)
            if ($notification->getRecipient() === $this) {
                $notification->setRecipient(null);
            }
        }

        return $this;
    }

    public function getDateDembauche(): ?\DateTime
    {
        return $this->Date_dembauche;
    }

    public function setDateDembauche(\DateTime $Date_dembauche): static
    {
        $this->Date_dembauche = $Date_dembauche;

        return $this;
    }

}
