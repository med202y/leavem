<?php

namespace App\Enum;


enum typeconge: string
{
    // Congés administratifs
    case ANNUEL = 'congé annuel';
    case EXCEPTIONNEL = 'congé exceptionnel';
    case PERMISSION = 'permission d\'absence';

    // Congés pour raisons de santé
    case MALADIE_COURTE = 'maladie courte durée';
    case MALADIE_MOYENNE_LONGUE = 'maladie moyenne/longue durée';
    case ACCIDENT_TRAVAIL = 'accident du travail';

    // Congé maternité
    case MATERNITE = 'congé maternité';

    // Congé sans solde
    case SANS_SOLDE = 'congé sans solde';

    public function label(): string
    {
        return match($this) {
            self::ANNUEL => 'Congé annuel',
            self::EXCEPTIONNEL => 'Congé exceptionnel',
            self::PERMISSION => 'Permission d’absence',
            self::MALADIE_COURTE => 'Maladie courte durée',
            self::MALADIE_MOYENNE_LONGUE => 'Maladie moyenne ou longue durée',
            self::ACCIDENT_TRAVAIL => 'Maladie ou blessure (accident du travail)',
            self::MATERNITE => 'Congé maternité',
            self::SANS_SOLDE => 'Congé sans solde',
        };
    }
}
