<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\Document;
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
            $page = [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => $error
            ];
            return $page;
        }

        $page = [
            'view_name' => 'move',
            'title' => $this->uri_data->title,
            'subtitle' => Languages::get('page', 'move'),
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                ],
                'captcha' => null,
                'token' => null
            ],
            'menus' => [],
            'customData' => []
        ];

        if (!empty($_POST['token']) && $this->session['token'] !== $_POST['token']) {
            $error = [
                'code' => 'err_csrf_token',
                'errbox' => true
            ];
        } elseif (isset($_POST['token']) && $this->session['token'] == $_POST['token'] && isset($_POST['new_title'])) {
            // Approve Move
            $member = $this->session['member']['uuid'] ?? null;
            $ip = !$member ? $this->session['ip'] : null;

            Document::move(
                $uuid, 
                $this->uri_data->title, 
                $_POST['new_title'], 
                $member,
                $ip,
                $_POST['summary']);
            Header('Location: /w/'.$_POST['new_title']);
        }else{
            $this->session['token'] = self::rand(64);
            $page['data']['token'] = $this->session['token'];
        }

        return $page;
    }
}