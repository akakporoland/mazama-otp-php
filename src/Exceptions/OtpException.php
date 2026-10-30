<?php

namespace Mazama\Otp\Exceptions;

use RuntimeException;

/** Base de toutes les erreurs du paquet : un seul `catch (OtpException)` suffit. */
class OtpException extends RuntimeException
{
}
