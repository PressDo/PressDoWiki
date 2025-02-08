<?php
namespace PressDo\app\Controllers\Pages\Member;

use PressDo\app\Models\Member;
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\{Languages,Config};
use lbuchs\WebAuthn\WebAuthn;

class Mypage extends Controller
{
    public function makeData(): array
    {
        if (!$this->session['member']) {
            Header('Location: /member/login?redirect='.$_SERVER['REQUEST_URI']);
            exit;
        }

        if (!empty($_POST['delnm'])) {
            Member::deleteUserWebauthn($this->session['member']['uuid'], $_POST['delnm']);
        } elseif (json_decode($_POST['challenge'], true) !== null && !empty($_POST['passkeyName'])) {
            // validate passkey enrollment
            $passkeys = Member::getUserWebauthn($this->session['member']['uuid']);
            foreach ($passkeys as $p) {
                if ($p['name'] == $_POST['passkeyName']) {
                    $errmsg = 'authenticator_duplicate_name';
                    break;
                }
            }

            if (!$errmsg) {
                $webauthn = new WebAuthn(Config::get('wiki.site_name'), Config::get('wiki.domain'));
    
                $data = json_decode($_POST['challenge'], true);
                $challenge = $this->session['challenge'];
    
                $credential = $webauthn->processCreate(base64_decode($data['response']['clientDataJSON']), base64_decode($data['response']['attestationObject']), $challenge);
                $credential->id = $data['id'];
                $credential->credentialId = base64_encode($credential->credentialId);
                $credential->AAGUID = base64_encode($credential->AAGUID);
                
                // 등록된 키를 데이터베이스에 저장
                Member::saveUserWebauthn($this->session['member']['uuid'], $_POST['passkeyName'], json_encode($credential));
            }                
        }

        $user = Member::getUserInfo($this->session['member']['uuid']);
        $passkeys = Member::getUserWebauthn($this->session['member']['uuid']);

        if ($user['totp_secret']) {
            // initilaize passkey enrollment
            $dowebauthn = true;
            $webauthn = new WebAuthn(Config::get('wiki.site_name'), Config::get('wiki.domain'));
            $passkey_id = random_bytes(20);
            $idset = [];
            
            foreach ($passkeys as $p) {
                $pd = json_decode($p['client_data'], false);
                array_push($idset, base64_decode($pd->credentialId));
            }
            
            $resp = $webauthn->getCreateArgs(
                $passkey_id, 
                $this->session['member']['username'], 
                $this->session['member']['username'],
                60 * 4,
                excludeCredentialIds: $idset
            );
            
            $challenge = $webauthn->getChallenge();
            $this->session['challenge'] = $challenge;
        }

        $dirs = scandir('skins');

        if (count($dirs) == 2)
            $skins = [];
        else
            $skins = array_slice($dirs, 2);

        $page = [
            'view_name' => 'mypage',
            'title' => Languages::get('page')['mypage'],
            'data' => [
                'error' => $errmsg,
                'skins' => $skins,
                'totp' => ($user['totp_secret']),
                'email' => $user['email'],
                'perms' => explode(',', $user['perm']),
                'webauthn' => $passkeys,
                'webauthn_arg' => $dowebauthn ? json_encode($resp) : null
            ],
            'menus' => [],
            'customData' => []
        ];
        
        return $page;
    }
}