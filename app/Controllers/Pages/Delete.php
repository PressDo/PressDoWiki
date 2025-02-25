<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Document,Member};
use PressDo\app\Core\Controller;
use PressDo\app\Controllers\ACL as WikiACL;
use PressDo\app\Helpers\{Languages,Config};

class Delete extends Controller
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
                $ACL->check('delete');
        }

        // 문서 없음
        if (!$uuid)
            $error = ['code' => 'no_such_document'];
        else {
            $doc = Document::load($uuid);
            if ($doc['status'] == 'delete')
                $error = ['code' => 'no_such_document'];
        }
        
        if (!empty($error['code'])) {
            return [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => $error
            ];
        }

        $page = [
            'view_name' => 'delete',
            'title' => $this->uri_data->title,
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                    'forceShowNamespace' => self::forceShowNamespace($namespace, $title)
                ],
                'captcha' => null
            ],
            'menus' => [],
            'customData' => []
        ];

        if (!empty($_POST['log']) && isset($_POST['agree'])) {
            // Approve Delete
            $member = $this->session['member'] ? $this->session['uuid'] : null;
            $ip = !$member ? ($this->session['uuid'] ?? Member::getIpUuid($this->session['ip'])) : null;
            if (!$member && !$this->session['uuid']) {
                $this->session['uuid'] = $ip;
            }

            Document::delete(
                $uuid, 
                $member,
                $ip,
                iconv_strlen($doc['content']),
                $doc['rev'],
                $_POST['log']);
            Header('Location: /w/'.$this->uri_data->title);
            exit;
        }

        return $page;
    }
}