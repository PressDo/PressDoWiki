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
    public function makeData()
    {
        if(!empty($this->session['member']))
            Header('Location: /');

        $page = [
            'view_name' => 'login',
            'title' => Languages::get('page')['login'],
            'data' => [
                'redirect' => $_GET['redirect']
            ],
            'menus' => [],
            'customData' => []
        ];

        if (!empty($this->session['temp']['uuid'])) {
            // 2차인증 처리
            if (json_decode($_POST['challenge']) !== null && $this->session['do2fa'] == 'webauthn') {
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

            } elseif (strlen($_POST['pin']) === 6 && is_numeric($_POST['pin']) && ($this->session['do2fa'] == 'totp' || $this->session['do2fa'] == 'webauthn')) {
                $user = Member::getUserInfo($this->session['temp']['uuid']);
                $otp = TOTP::createFromSecret($user['totp_secret']);
                $ok = $otp->verify($_POST['pin']);

                if (!$ok)
                    $page['data']['error'] = 'err_invalid_pin';
            } elseif ($this->session['do2fa'] == 'email' && strlen($_POST['pin']) === 6 && is_numeric($_POST['pin'])) {
                if ($this->session['pin'] !== $_POST['pin'])
                    $page['data']['error'] = 'err_invalid_pin';
                
            } else {
                unset($this->session['temp']);
            }

            // 끝
            if (!empty($page['data']['error'])) {
                $this->setup2fa($this->session['temp']['member'], $page['data']);
            } elseif (!empty($this->session['temp'])) {
                $this->session['menus'] = $this->session['temp']['menus'];
                $this->session['member'] = $this->session['temp']['member'];
                $this->session['uuid'] = $this->session['temp']['uuid'];
                unset($this->session['temp']);
                
                // 로그인 성공
                Header('Location: '.($_GET['redirect'] ?? '/'));
            }
        } elseif (isset($_POST['username']) && isset($_POST['password'])) {
            // 1차 로그인
            $l = Member::login($_POST['username'], $_POST['password'], $this->session['ip'], $_SERVER['HTTP_USER_AGENT']);

            if (!$l) {
                $page['data']['error'] = 'err_invalid_member';
                return $page;
            }
            
            $menus = [];
            $SP = ['aclgroup', 'grant', 'login_history'];
            $link = ['aclgroup' => '/aclgroup', 'grant' => '/admin/grant', 'login_history' => '/admin/login_history'];
            $sps = Member::specialPerms($l['uuid']);
            foreach ($SP as $prm) {
                if (in_array($prm, $sps))
                    array_push($menus, ['l' => $link[$prm], 't' => $prm]);
            }

            unset($this->session['temp']);
            $this->session['temp'] = [
                'menus' => $menus,
                'email' => $l['email'],
                'uuid' => $l['uuid'],
                'member' => [
                    'user_document_discuss' => null,
                    'username' => $l['username'],
                    'gravatar_url' => '//www.gravatar.com/avatar/'.md5($l['email']).'?d=retro',
                    'admin' => in_array('admin', $sps),
                    'settings' => ['skin' => $l['skin']]
                ]
            ];

            // 2차 로그인
            if (self::newLoginEnv()) { // && !in_array('disable_two_factor_login', $sps)
                $this->setup2fa($l, $page['data']);
                return $page;
            }

            $this->session['menus'] = $this->session['temp']['menus'];
            $this->session['member'] = $this->session['temp']['member'];
            $this->session['uuid'] = $this->session['temp']['uuid'];
            unset($this->session['temp']);
            
            // 로그인 성공
            Header('Location: '.($_GET['redirect'] ?? '/'));
        
        }
        return $page;
    }

    private static function newLoginEnv(): bool
    {
        return true;
    }

    private function setup2fa(array $userdata, array &$data)
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
}