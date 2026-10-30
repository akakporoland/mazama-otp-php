<?php

namespace Mazama\Otp\Tests;

use Illuminate\Http\Client\Factory;
use Mazama\Otp\Client;
use Mazama\Otp\Exceptions\InvalidPhoneException;
use Mazama\Otp\Exceptions\InvalidTokenException;
use Mazama\Otp\Exceptions\SendFailedException;
use Mazama\Otp\Exceptions\TooManyRequestsException;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private function client(int $status, array $body = [], string $token = 'mzm_abc'): Client
    {
        $http = new Factory();
        $http->fake(['api.test/*' => $http->response($body, $status)]);

        return new Client('https://api.test/api/v1/', $token, $http);
    }

    public function test_send_renvoie_le_numero_masque_et_la_duree(): void
    {
        $r = $this->client(200, ['ok' => true, 'phone' => '228••••••00', 'expires_in' => 300])->send('+22890000000');

        $this->assertSame(['phone' => '228••••••00', 'expires_in' => 300], $r);
    }

    public function test_verify_renvoie_un_booleen(): void
    {
        $this->assertTrue($this->client(200, ['valid' => true])->verify('+22890000000', '123456'));
        $this->assertFalse($this->client(200, ['valid' => false])->verify('+22890000000', '000000'));
    }

    public function test_le_token_et_les_donnees_sont_envoyes_a_la_bonne_url(): void
    {
        $http = new Factory();
        $http->fake(['*' => $http->response(['valid' => true])]);

        (new Client('https://api.test/api/v1', 'mzm_abc', $http))->verify('+228900', '123456');

        $http->assertSent(fn ($r) => $r->url() === 'https://api.test/api/v1/otp/verify'
            && $r->hasHeader('Authorization', 'Bearer mzm_abc')
            && $r['phone'] === '+228900' && $r['code'] === '123456');
    }

    public function test_les_erreurs_http_deviennent_des_exceptions(): void
    {
        $cas = [
            [401, InvalidTokenException::class],
            [422, InvalidPhoneException::class],
            [429, TooManyRequestsException::class],
            [503, SendFailedException::class],
        ];

        foreach ($cas as [$status, $classe]) {
            try {
                $this->client($status)->send('+22890000000');
                $this->fail("HTTP $status aurait dû lever $classe");
            } catch (\Throwable $e) {
                $this->assertInstanceOf($classe, $e);
            }
        }
    }

    public function test_retry_after_est_expose(): void
    {
        try {
            $this->client(429, ['retry_after' => 120])->send('+22890000000');
        } catch (TooManyRequestsException $e) {
            $this->assertSame(120, $e->retryAfter);
        }
    }

    public function test_configuration_manquante(): void
    {
        $this->expectException(InvalidTokenException::class);

        $this->client(200, [], '')->send('+22890000000');
    }
}
