<?php

namespace Mazama\Otp;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Response;
use Mazama\Otp\Exceptions\InvalidPhoneException;
use Mazama\Otp\Exceptions\InvalidTokenException;
use Mazama\Otp\Exceptions\SendFailedException;
use Mazama\Otp\Exceptions\TooManyRequestsException;

/** Client de l'API mazama-otp : send() envoie le code, verify() contrôle ce que l'utilisateur a saisi. */
class Client
{
    public function __construct(
        private readonly string $url,
        private readonly string $token,
        private readonly Factory $http = new Factory(),
        private readonly int $timeout = 15,
    ) {
    }

    /**
     * Envoie un code WhatsApp au numéro (format international conseillé : +22890000000).
     *
     * @return array{phone: string, expires_in: int} numéro masqué et durée de validité en secondes
     */
    public function send(string $phone): array
    {
        $r = $this->post('/otp/send', ['phone' => $phone]);

        return ['phone' => (string) $r->json('phone'), 'expires_in' => (int) $r->json('expires_in')];
    }

    /** Vrai si le code est bon. Un code correct ne marche qu'une fois. */
    public function verify(string $phone, string $code): bool
    {
        return (bool) $this->post('/otp/verify', ['phone' => $phone, 'code' => $code])->json('valid');
    }

    private function post(string $path, array $data): Response
    {
        if ($this->url === '' || $this->token === '') {
            throw new InvalidTokenException('MAZENDA_URL ou MAZENDA_TOKEN manquant dans le .env.');
        }

        try {
            $r = $this->http->withToken($this->token)->acceptJson()->timeout($this->timeout)
                ->post(rtrim($this->url, '/') . $path, $data);
        } catch (ConnectionException) {
            throw new SendFailedException('Service mazama-otp injoignable.');
        }

        return match (true) {
            $r->successful() => $r,
            $r->status() === 401 => throw new InvalidTokenException(),
            $r->status() === 422 => throw new InvalidPhoneException((string) ($r->json('error') ?? 'Données invalides.')),
            $r->status() === 429 => throw new TooManyRequestsException($r->json('retry_after')),
            default => throw new SendFailedException(),
        };
    }
}
