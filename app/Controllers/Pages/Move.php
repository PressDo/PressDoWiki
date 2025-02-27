<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Document, Member};
use PressDo\app\Core\Controller;
use PressDo\app\Controllers\ACL as WikiACL;
use PressDo\app\Helpers\Languages;

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
            $this->error = [
                'code' => 'err_csrf_token',
                'message' => Languages::get('msg', 'err_csrf_token'),
                'errbox' => true
            ];
        } elseif (!empty($_POST['token']) && $this->session['movetoken'] == $_POST['token'] && !empty($_POST['new_title'])) {
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
        }else{
            $this->session['movetoken'] = self::rand(64);
            $page['data']['token'] = $this->session['movetoken'];
        }

        return $page;
    }
}