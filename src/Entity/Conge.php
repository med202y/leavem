<?php

namespace App\Entity;

use App\Enum\typeconge;
use App\Enum\statutdedemande;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CongeRepository;

#[ORM\Entity(repositoryClass: CongeRepository::class)]
#[ORM\Table(name: 'conge')]
class Conge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'datetime', name: 'date_debut')]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: 'datetime', name: 'date_fin')]
    private \DateTimeInterface $dateFin;

    #[ORM\Column(type: 'string', enumType: typeconge::class, name: 'type_conge')]
    private typeconge $typeConge;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $motif = null;

    #[ORM\Column(type: 'string', enumType: statutdedemande::class)]
    private statutdedemande $statut;

    #[ORM\Column(type: 'datetime', name: 'date_demande')]
    private \DateTimeInterface $dateDemande;

    #[ORM\Column(type: 'datetime', name: 'date_traitement', nullable: true)]
    private ?\DateTimeInterface $dateTraitement = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $traitePar = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $justificatif = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getDateDebut(): \DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): \DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getTypeConge(): typeconge
    {
        return $this->typeConge;
    }

    public function setTypeConge(typeconge $typeConge): self
    {
        $this->typeConge = $typeConge;
        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): self
    {
        $this->motif = $motif;
        return $this;
    }

    public function getStatut(): statutdedemande
    {
        return $this->statut;
    }

    public function setStatut(statutdedemande $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateDemande(): \DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeInterface $dateDemande): self
    {
        $this->dateDemande = $dateDemande;
        return $this;
    }

    public function getDateTraitement(): ?\DateTimeInterface
    {
        return $this->dateTraitement;
    }

    public function setDateTraitement(?\DateTimeInterface $dateTraitement): self
    {
        $this->dateTraitement = $dateTraitement;
        return $this;
    }

    public function getTraitePar(): ?string
    {
        return $this->traitePar;
    }

    public function setTraitePar(?string $traitePar): self
    {
        $this->traitePar = $traitePar;
        return $this;
    }

    public function getJustificatif(): ?string
    {
        return $this->justificatif;
    }

    public function setJustificatif(?string $justificatif): self
    {
        $this->justificatif = $justificatif;
        return $this;
    }
}
