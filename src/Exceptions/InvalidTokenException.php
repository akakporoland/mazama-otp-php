<?php

namespace Mazama\Otp\Exceptions;

class InvalidTokenException extends OtpException
{
    public function __construct(string $message = 'Token invalide ou révoqué.')
    {
        parent::__construct($message);
    }
}
