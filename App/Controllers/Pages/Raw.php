<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Models\Document;
use PressDo\App\Core\Controller;
use PressDo\App\Controllers\ACL as WikiACL;
use PressDo\App\Helpers\Languages;

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
                    'forceShowNamespace' => self::forceShowNamespace($namespace, $title)
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
        $page['data']['rev'] = $doc['rev'];
        $page['data']['text'] = $doc['content'];
        //'debug' => $this->uri_data
        return $page;
    }
}