<?php
namespace PressDo\app\Controllers\Pages\Member;

use PressDo\app\Models\Member;
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\{Languages,Config};
use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\WebAuthnException;
use OTPHP\TOTP;

class Login extends Controller
{
    public function makeData(): array
    {
        if (!empty($this->session['member'])) {
            Header('Location: /');
            exit;
        }

        $page = [
            'view_name' => 'login',
            'title' => Languages::get('page', 'login'),
            'data' => [
                'redirect' => $_GET['redirect']
            ],
            'menus' => [],
            'customData' => []
        ];

        // 2차인증 처리
        if (!empty($this->session['temp']['uuid'])) {
            if (json_decode($_POST['challenge']) !== null && $this->session['do2fa'] == 'webauthn')
                $this->verifyWebAuthn($page);
            elseif (strlen($_POST['pin']) === 6 && is_numeric($_POST['pin']))
                $this->verifyPin($page);
            else
                unset($this->session['temp']);

            // 끝
            if (!empty($page['data']['error'])) {
                $this->setup2fa($this->session['temp']['member'], $page['data']);
            } elseif (!empty($this->session['temp'])) {
                $this->finishLogin();
            }
        } elseif (isset($_POST['username']) && isset($_POST['password'])) {
            // 1차 로그인
            $c = Member::checkMember($_POST['username'], $_POST['password']);
            if (!$c) {
                $page['data']['error'] = 'err_invalid_member';
                return $page;
            }

            $l = Member::login($c['uuid'], $this->session['ip'], $_SERVER['HTTP_USER_AGENT']);
            $l['uuid'] = $c['uuid'];

            unset($this->session['temp']);
            $this->session['temp'] = self::getMemberData($c['uuid'], $l['email'], $l['username'], $l['skin']);
            
            if (isset($_POST['autologin']))
                $this->session['szczecin'] = true;
            else
                unset($this->session['szczecin']);

            // 2차 로그인
            if (empty($_COOKIE['podgorica']) || Member::checkCookie('podgorica', $_COOKIE['podgorica']) === null) { // && !in_array('disable_two_factor_login', $sps)
                $this->setup2fa($l, $page['data']);
                return $page;
            }

            $this->finishLogin();
        }
        return $page;
    }

    private function setup2fa(array $userdata, array &$data): void
    {
        $user = Member::getUserInfo($userdata['uuid']);
        $passkeys = Member::getUserWebauthn($userdata['uuid']);
        $this->session['do2fa'] = !empty($user['totp_secret']) ? (!empty($passkeys) ? 'webauthn' : 'totp') : 'email';

        if ($this->session['do2fa'] == 'webauthn') {
            $webauthn = new WebAuthn(Config::get('wiki.site_name'), Config::get('wiki.domain'));
            $idset = [];

            foreach ($passkeys as $p) {
                $pd = json_decode($p['client_data'], false);
                array_push($idset, base64_decode($pd->credentialId));
            }

            $resp = $webauthn->getGetArgs($idset);
            $this->session['challenge'] = $webauthn->getChallenge();
        } elseif ($this->session['do2fa'] == 'email') {
            $this->session['pin'] = str_pad(rand(0, 999999), 6, 0, STR_PAD_LEFT);
            $lang = Languages::get('mail');
            $content = $lang['new_login'];
            $data['otp_email'] = $userdata['email'] ?? $this->session['temp']['email'];

            $body = sprintf($content, Config::get('wiki.site_name'), $this->session['pin'], $this->session['ip']);

            if (empty($this->session['pin']))
                $send = self::sendMail(
                    $userdata['email'] ?? $this->session['temp']['email'], 
                    sprintf($lang['new_login_title'], Config::get('wiki.site_name')),
                    $body
                );
            
            if(!$send)
                $data['error'] = 'err_mail_not_send';
        }

        $data['force_2fa'] = true;
        $data['use_totp'] = !empty($user['totp_secret']);
        $data['use_webauthn'] = !empty($passkeys);
        $data['webauthn_arg'] = json_encode($resp) ?? null;
    }

    private function verifyWebAuthn(array &$page): void
    {
        $passkeys = Member::getUserWebauthn($this->session['temp']['uuid']);
        $webauthn = new WebAuthn(Config::get('wiki.site_name'), Config::get('wiki.domain'));
        $data = json_decode($_POST['challenge'], true);
        $credentialPublicKey = null;

        $clientDataJSON = !empty($data['response']['clientDataJSON']) ? base64_decode($data['response']['clientDataJSON']) : null;
        $authenticatorData = !empty($data['response']['authenticatorData']) ? base64_decode($data['response']['authenticatorData']) : null;
        $signature = !empty($data['response']['signature']) ? base64_decode($data['response']['signature']) : null;
        //$userHandle = !empty($data['response']['userHandle']) ? base64_decode($data['response']['userHandle']) : null;
        $id = !empty($data['id']) ? base64_decode($data['id']) : null;
        $challenge = $this->session['challenge'] ?? '';
        $credentialPublicKey = null;

        foreach ($passkeys as $p) {
            $pd = json_decode($p['client_data'], false);
            if (base64_decode($pd->credentialId) === $id) {
                $credentialPublicKey = $pd->credentialPublicKey;
                $usedname = $p['name'];
                break;
            }
        }

        // throws exception if failed
        try {
            $webauthn->processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $challenge);
            Member::logWebauthnUsage($usedname);
        } catch (WebAuthnException $e) {
            $page['data']['error'] = 'WebAuthn Error: '.$e->getMessage();
        }
    }

    private function verifyPin(array &$page): void
    {
        if ($this->session['do2fa'] == 'totp' || $this->session['do2fa'] == 'webauthn') {
            $user = Member::getUserInfo($this->session['temp']['uuid']);
            $otp = TOTP::createFromSecret($user['totp_secret']);
            $ok = $otp->verify($_POST['pin']);

            if (!$ok)
                $page['data']['error'] = 'err_invalid_pin';
        } elseif ($this->session['do2fa'] == 'email') {
            if ($this->session['pin'] !== $_POST['pin'])
                $page['data']['error'] = 'err_invalid_pin';
        }
    }

    private function finishLogin(): never
    {
        $this->session['menus'] = $this->session['temp']['menus'];
        $this->session['member'] = $this->session['temp']['member'];
        $this->session['uuid'] = $this->session['temp']['uuid'];
        unset($this->session['temp']);

        if (isset($_POST['autologin']) || $this->session['szczecin'] === true) {
            setcookie(
                'szczecin',
                Member::saveCookies($this->session['uuid'], 'szczecin', 31536000),
                self::getCookieOptions(31536000)
            );
            unset($this->session['szczecin']);
        }
        if (isset($_POST['trust']) || (json_decode($_POST['challenge']) !== null && $this->session['do2fa'] == 'webauthn')) {
            setcookie(
                'podgorica',
                Member::saveCookies($this->session['uuid'], 'podgorica', 34560000),
                self::getCookieOptions(34560000)
            );
        }
        
        // 로그인 성공
        $_SESSION = $this->session;
        Header('Location: '.($_GET['redirect'] ?? '/'));
        exit;
    }
}