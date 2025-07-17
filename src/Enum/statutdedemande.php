<?php

namespace App\Enum;

enum statutdedemande: string
{
    case EN_ATTENTE = 'en_attente';
    case APPROUVEE = 'approuvée';
    case REJETEE = 'rejetée';
    case ANNULEE = 'annulée';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::APPROUVEE => 'Approuvée',
            self::REJETEE => 'Rejetée',
            self::ANNULEE => 'Annulée',
        };
    }
}
