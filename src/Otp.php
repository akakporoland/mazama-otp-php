<?php

namespace Mazama\Otp;

use Illuminate\Support\Facades\Facade;

/**
 * Otp::send('+22890000000');
 * Otp::verify('+22890000000', '123456');   // true / false
 *
 * @method static array send(string $phone)
 * @method static bool verify(string $phone, string $code)
 */
class Otp extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
