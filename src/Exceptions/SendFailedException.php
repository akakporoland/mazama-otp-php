<?php

namespace Mazama\Otp\Exceptions;

class SendFailedException extends OtpException
{
    public function __construct(string $message = 'Envoi impossible pour le moment.')
    {
        parent::__construct($message);
    }
}
