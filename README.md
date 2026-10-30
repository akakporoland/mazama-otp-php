# mazama/otp

Envoie et vérifie des codes OTP par WhatsApp avec le service mazama-otp (Laravel 11, 12 ou 13).

## Installation
```bash
composer require mazama/otp
```
Tant que le paquet n'est pas sur Packagist, ajoute d'abord dans ton `composer.json` :
```json
"repositories": [{"type": "vcs", "url": "https://github.com/TON-COMPTE/mazama-otp"}]
```

## Configuration
Dans ton `.env`, avec les valeurs données par l'administrateur :
```
MAZAMA_OTP_URL=https://otp.akakporoland.it/api/v1
MAZAMA_OTP_TOKEN=mzm_xxxxxxxxxxxxxxxx
```

## Utilisation
```php
use Mazama\Otp\Otp;
use Mazama\Otp\Exceptions\OtpException;

// 1. L'utilisateur saisit son numéro
Otp::send($request->phone);          // ['phone' => '228••••••00', 'expires_in' => 300]

// 2. L'utilisateur tape le code reçu sur WhatsApp
if (Otp::verify($request->phone, $request->code)) {
    // code bon (valable 5 min, 5 essais, usage unique) : connecte l'utilisateur
}
```

## Erreurs
Toutes étendent `OtpException`.

| Exception | Cause |
|---|---|
| `InvalidTokenException` | token invalide, révoqué, ou `.env` incomplet |
| `InvalidPhoneException` | numéro invalide |
| `TooManyRequestsException` | trop de demandes pour ce numéro (`->retryAfter` en secondes) |
| `SendFailedException` | service injoignable ou envoi impossible |

```php
try {
    Otp::send($phone);
} catch (TooManyRequestsException $e) {
    // "Réessaie dans {$e->retryAfter} secondes"
} catch (OtpException $e) {
    // autre erreur
}
```

## Tests
```bash
composer install && vendor/bin/phpunit
```
