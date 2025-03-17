<?php
namespace PressDo\App\Helpers\Captcha;

interface CaptchaInterface
{
    public static function verify(string $token): bool;
}