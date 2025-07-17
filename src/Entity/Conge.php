<?php

namespace App\Entity;

use App\Enum\statutdedemande;
use App\Enum\typeconge;
use App\Repository\CongeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CongeRepository::class)]
class Conge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $date_debut = null;

    #[ORM\Column]
    private ?\DateTime $date_fin = null;

    #[ORM\Column(enumType: typeconge::class)]
    private ?typeconge $type_conge = null;

    #[ORM\Column(length: 255)]
    private ?string $motif = null;

    #[ORM\Column(enumType: statutdedemande::class)]
    private ?statutdedemande $statut = null;

    #[ORM\Column]
    private ?\DateTime $date_demande = null;

    #[ORM\Column]
    private ?\DateTime $date_traitement = null;

    #[ORM\Column(length: 255)]
    private ?string $traite_par = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTime $date_debut): static
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTime $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getTypeConge(): ?typeconge
    {
        return $this->type_conge;
    }

    public function setTypeConge(typeconge $type_conge): static
    {
        $this->type_conge = $type_conge;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): static
    {
        $this->motif = $motif;

        return $this;
    }

    public function getStatut(): ?statutdedemande
    {
        return $this->statut;
    }

    public function setStatut(statutdedemande $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateDemande(): ?\DateTime
    {
        return $this->date_demande;
    }

    public function setDateDemande(\DateTime $date_demande): static
    {
        $this->date_demande = $date_demande;

        return $this;
    }

    public function getDateTraitement(): ?\DateTime
    {
        return $this->date_traitement;
    }

    public function setDateTraitement(\DateTime $date_traitement): static
    {
        $this->date_traitement = $date_traitement;

        return $this;
    }

    public function getTraitePar(): ?string
    {
        return $this->traite_par;
    }

    public function setTraitePar(string $traite_par): static
    {
        $this->traite_par = $traite_par;

        return $this;
    }
}
