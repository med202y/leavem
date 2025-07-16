<?php

namespace App\Enum;

enum ValidationStrategy: string
{
    case SIMPLE = 'SIMPLE';           // Une seule personne valide
    case DOUBLE = 'DOUBLE';           // Deux validations nécessaires
    case LIBRE = 'LIBRE';             // Pas de validation (auto-validation ou spécial)

    public function label(): string
    {
        return match($this) {
            self::SIMPLE => 'Validation simple (1 personne)',
            self::DOUBLE => 'Validation double (2 personnes requises)',
            self::LIBRE => 'Sans validation requise',
        };
    }
}


