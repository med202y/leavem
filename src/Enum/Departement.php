<?php

namespace App\Enum;

enum Departement: string
{
    case SYMFONY = 'Symfony development';
    case INTEGRATEUR = 'Intégrateur';

    public function label(): string
    {
        return match($this) {
            self::SYMFONY => 'Symfony development',
            self::INTEGRATEUR => 'Intégrateur',
        };
    }
}
