<?php

namespace Mazama\Otp\Exceptions;

class TooManyRequestsException extends OtpException
{
    /** @param int|null $retryAfter secondes à attendre avant de réessayer */
    public function __construct(public readonly ?int $retryAfter = null)
    {
        parent::__construct('Trop de demandes pour ce numéro.');
    }
}
