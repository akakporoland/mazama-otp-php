<?php

namespace Mazama\Otp\Exceptions;

class InvalidPhoneException extends OtpException
{
    public function __construct(string $message = 'Numéro de téléphone invalide.')
    {
        parent::__construct($message);
    }
}
