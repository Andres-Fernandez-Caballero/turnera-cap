<?php

namespace App\Models\Payments\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel{
    case MERCADO_PAGO= "mercado_pago";
    case PAGO_EN_ADMINISTRACION= "pago_administracion";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MERCADO_PAGO => 'Mercado Pago',
            self::PAGO_EN_ADMINISTRACION => 'Pago en Administracion',
        };
    }

    public static function values(): array
    {
        return [
            self::MERCADO_PAGO->value,
            self::PAGO_EN_ADMINISTRACION->value,
        ];
    }

}
