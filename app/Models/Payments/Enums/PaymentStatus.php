<?php

namespace App\Models\Payments\Enums;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasLabel {
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PENDING = 'pending';

    public function getLabel(): string {
        return match($this){
            self::PENDING_APPROVAL => 'Pendiente de Aprobacion',
            self::APPROVED => 'Aprobado',
            self::REJECTED => 'Rechazado',
            self::PENDING => 'Pendiente',
        };
    }

    public static function values(): array
    {
        return [
            self::PENDING_APPROVAL->value,
            self::APPROVED->value,
            self::REJECTED->value,
            self::PENDING->value,
        ];
    }
}