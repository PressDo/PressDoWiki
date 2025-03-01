<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Models\{Document, Member};
use PressDo\App\Core\Controller;
use PressDo\App\Controllers\ACL as WikiACL;
use PressDo\App\Helpers\{Languages,Namespaces};

class Move extends Controller
{
    public function makeData(): array
    {
        [$namespace, $title] = self::parseTitle($this->uri_data->title);
        $uuid = Document::getUuid($namespace, $title, $backlinkrefreshed);
        $error = [];

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $error);
        $ACL->check('read');

        if ($error['code'] !== 'permission_read') {
            $ACL->check('edit');
            if ($error['code'] !== 'permission_edit')
                $ACL->check('move');
        }

        // 문서 없음
        if (!$uuid)
            $error = ['code' => 'no_such_document'];
        
        if (!$uuid || $error['code'] == 'permission_read' || $error['code'] == 'permission_edit' || $error['code'] == 'permission_move') {
            return [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => $error
            ];
        }

        $page = [
            'view_name' => 'move',
            'title' => $this->uri_data->title,
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                    'forceShowNamespace' => self::forceShowNamespace($namespace, $title)
                ],
                'captcha' => null,
                'token' => null
            ],
            'menus' => [],
            'customData' => []
        ];

        if (!empty($_POST['token']) && $this->session['movetoken'] !== $_POST['token']) {
            $this->error = self::makeErrorBox('err_csrf_token');
        } elseif (!empty($_POST['token']) && $this->session['movetoken'] == $_POST['token'] && !empty($_POST['new_title'])) {
            // 이동 목적지 ACL 체크
            [$tons, $totitle] = self::parseTitle($_POST['new_title']);
            $ACL2 = new WikiACL($tons, $totitle, $uuid, $this->session, $error);
            $ACL2->check('read');
            $ACL2->check('edit');

            // 잘못된 이름공간 사이의 이동
            if ($namespace !== $tons && !self::checkInterNamespace($namespace, $tons))
                $error = ['code' => 'cannot_move_to_namespace'];

            // 파일 문서 이동 제한
            if ($namespace == Namespaces::file()) {
                $oldfileext = explode('.', $title);
                $newfileext = explode('.', $totitle);
                if (end($oldfileext) !== end($newfileext)) {
                    $error = [
                        'code' => $error,
                        'message' => sprintf(Languages::get('msg', $error), strtolower(end($oldfileext))) ?? '',
                        'errbox' => true
                    ];
                }
            }

            if (!empty($error)) {
                return [
                    'view_name' => 'error',
                    'title' => Languages::get('page', 'error'),
                    'data' => $error
                ];
            }

            if (empty($this->error)) {
                // Approve Move
                $member = $this->session['member'] ? $this->session['uuid'] : null;
                $ip = !$member ? ($this->session['uuid'] ?? Member::getIpUuid($this->session['ip'])) : null;
                if (!$member && !$this->session['uuid']) {
                    $this->session['uuid'] = $ip;
                }
                $rev = Document::getVersion($uuid);
    
                Document::move(
                    $uuid, 
                    $this->uri_data->title, 
                    $_POST['new_title'], 
                    $member,
                    $ip,
                    $rev,
                    $_POST['summary']);
                Header('Location: /w/'.$_POST['new_title']);
                unset($this->session['movetoken']);
            }
        }else{
            $this->session['movetoken'] = self::rand(64);
            $page['data']['token'] = $this->session['movetoken'];
        }

        return $page;
    }

    private static function checkInterNamespace(string $befns, string $aftns): bool
    {
        if ($befns == Namespaces::user() || $aftns == Namespaces::user())
            return false;
        if ($befns == Namespaces::file() || $aftns == Namespaces::file())
            return false;

        return true;
    }
}