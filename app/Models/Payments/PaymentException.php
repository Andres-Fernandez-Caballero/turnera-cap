<?php

namespace App\Models\Payments;

use App\Models\Payments\Enums\PaymentMethod;

class PaymentException extends \Exception
{
    protected ?PaymentMethod $paymentProvider;
    protected ?array $errorDetails;

    public function __construct(
        string $message,
        int $code = 0,
        ?PaymentMethod $paymentProvider = null,
        ?array $errorDetails = null,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->paymentProvider = $paymentProvider;
        $this->errorDetails = $errorDetails;
    }

    /**
     * Obtiene el proveedor de pago que causó el error.
     */
    public function getPaymentProvider(): ?PaymentMethod
    {
        return $this->paymentProvider;
    }

    /**
     * Obtiene detalles adicionales del error.
     */
    public function getErrorDetails(): ?array
    {
        return $this->errorDetails;
    }

    /**
     * Devuelve un mensaje de error detallado.
     */
    public function getDetailedMessage(): string
    {
        $details = $this->errorDetails ? json_encode($this->errorDetails) : 'No details available';
        return "[Payment Provider: {$this->paymentProvider}] {$this->message} (Details: {$details})";
    }
}