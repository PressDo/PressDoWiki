<?php
namespace PressDo\App\Helpers\Captcha;

use PressDo\App\Helpers\DefaultConfig;

class ReCaptcha implements CaptchaInterface
{
    public const CLASS_NAME = 'g-recaptcha';

    public const TOKEN_NAME = 'g-recaptcha-response';

    public const API_ENDPOINT = 'https://www.google.com/recaptcha/api.js';

    public static function verify(string $token): bool
    {
        $secretKey = DefaultConfig::get('captcha.secret');
        $response = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify",
            context: stream_context_create([
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => "secret={$secretKey}&response={$token}"
                ]
            ])
        );
        $result = json_decode($response, true);

        return isset($result['success']) && $result['success'];
    }
}