<?php
namespace PressDo\App\Helpers\Captcha;

use PressDo\App\Helpers\DefaultConfig;

class HCaptcha implements CaptchaInterface
{
    public const CLASS_NAME = 'h-captcha';

    public const TOKEN_NAME = 'h-captcha-response';

    public const API_ENDPOINT = 'https://js.hcaptcha.com/1/api.js';

    public static function verify(string $token): bool
    {
        $secretKey = DefaultConfig::get('captcha.secret');
        $response = file_get_contents(
            "https://api.hcaptcha.com/siteverify",
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