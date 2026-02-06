<?php 

namespace App\Enum;

enum RequestStatus: string
{
    case EN_ATTENTE = 'en_attente';
    case EN_COURS = 'en_cours';
    case ACCEPTE = 'approuvée';
    case REFUSE = "rejetée";
    case ANNULEE = 'annulée';
    case CONSOMME = 'consommée';
    case ANNULATIONENATTENTE = 'annulation en attente';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::EN_COURS => 'En cours',
            self::ACCEPTE => 'Approuvée',
            self::REFUSE => 'Rejetée',
            self::ANNULEE => 'Annulée',
            self::CONSOMME => 'Consommée',
            self::ANNULATIONENATTENTE => 'Annulation en attente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EN_ATTENTE, self::ANNULATIONENATTENTE => 'warning',
            self::EN_COURS => 'primary', // New color for En cours
            self::ACCEPTE => 'success',
            self::REFUSE => 'danger',
            self::ANNULEE => 'secondary',
            self::CONSOMME => 'info',
        };
    }

    public static function colorMap(): array
    {
        $map = [];
        foreach (self::cases() as $status) {
            $map[$status->value] = $status->color();
        }
        return $map;
    }
}
