<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\Document;
use PressDo\app\Core\Controller;
use PressDo\app\Controllers\ACL as WikiACL;
use PressDo\app\Helpers\Languages;

class Raw extends Controller
{
    public function makeData(): array
    {
        [$namespace, $title] = self::parseTitle($this->uri_data->title);
        $uuid = Document::getUuid($namespace, $title, $backlinkrefreshed);
        $error = [];

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $error);
        $ACL->check('read');
        $page = [
            'view_name' => 'raw',
            'title' => $this->uri_data->title,
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                ],
                'rev' => null,
                'text' => null
            ],
            'menus' => [],
            'customData' => []
        ];

        if ($error['code'] == 'permission_read') {
            $page = [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => $error
            ];
            return $page;
        }

        if (!$uuid) {
            return [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => ['code' => 'no_such_document']
            ];
        }

        $rev = $_GET['uuid'] ?? $uuid;
        $doc = Document::load($uuid, $rev);

        if ($doc === null) {
            return [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => ['code' => 'no_such_revision']
            ];
        }

        $page['subtitle'] .= 'r'.$doc['rev'].' RAW';
        $page['data']['rev'] = $doc['rev'];
        $page['data']['text'] = $doc['content'];
        //'debug' => $this->uri_data
        return $page;
    }
}