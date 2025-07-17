<?php

namespace App\Entity;

use App\Enum\Role;
use App\Enum\Departement;
use App\Repository\UserRepository;
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

    #[ORM\Column(length: 255)]
    private ?string $CIN = null;

    #[ORM\Column(length: 255)]
    private ?string $CNSS = null;

    #[ORM\Column]
    private ?int $SalaireM = null;

    #[ORM\Column(length: 255)]
    private ?string $Qualificationsprofessionnelles = null;

    #[ORM\Column]
    private ?\DateTime $Date_douverture_droit_au_conge = null;

    #[ORM\Column]
    private ?int $Nbr_de_jours_travaille = null;

    #[ORM\Column]
    private ?float $jourdeconge_travail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Observations = null;

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

    public function getCNSS(): ?string
    {
        return $this->CNSS;
    }

    public function setCNSS(string $CNSS): static
    {
        $this->CNSS = $CNSS;

        return $this;
    }

    public function getSalaireM(): ?int
    {
        return $this->SalaireM;
    }

    public function setSalaireM(int $SalaireM): static
    {
        $this->SalaireM = $SalaireM;

        return $this;
    }

    public function getQualificationsprofessionnelles(): ?string
    {
        return $this->Qualificationsprofessionnelles;
    }

    public function setQualificationsprofessionnelles(string $Qualificationsprofessionnelles): static
    {
        $this->Qualificationsprofessionnelles = $Qualificationsprofessionnelles;

        return $this;
    }

    public function getDateDouvertureDroitAuConge(): ?\DateTime
    {
        return $this->Date_douverture_droit_au_conge;
    }

    public function setDateDouvertureDroitAuConge(\DateTime $Date_douverture_droit_au_conge): static
    {
        $this->Date_douverture_droit_au_conge = $Date_douverture_droit_au_conge;

        return $this;
    }

    public function getNbrDeJoursTravaille(): ?int
    {
        return $this->Nbr_de_jours_travaille;
    }

    public function setNbrDeJoursTravaille(int $Nbr_de_jours_travaille): static
    {
        $this->Nbr_de_jours_travaille = $Nbr_de_jours_travaille;

        return $this;
    }

    public function getJourdecongeTravail(): ?float
    {
        return $this->jourdeconge_travail;
    }

    public function setJourdecongeTravail(float $jourdeconge_travail): static
    {
        $this->jourdeconge_travail = $jourdeconge_travail;

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

}
