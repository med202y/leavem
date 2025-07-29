<?php

namespace App\Enum;

enum statutdedemande: string
{
    case EN_ATTENTE = 'en_attente';
    case ACCEPTE = 'approuvée';
    case REFUSE = 'rejetée';
    case ANNULEE = 'annulée';
    case CONSOMME =  'consommée';
    case ANNULATIONENATTENTE = 'annulation en attente';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::ACCEPTE => 'Approuvée',
            self::REFUSE => 'Rejetée',
            self::ANNULEE => 'Annulée',
            self::CONSOMME => 'consommée',
            self::ANNULATIONENATTENTE => 'Annulation en attente',
        };
    }
}
