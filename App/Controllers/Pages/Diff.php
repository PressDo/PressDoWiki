<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Models\{Document,History};
use PressDo\App\Core\Controller;
use PressDo\App\Controllers\ACL as WikiACL;
use PressDo\App\Helpers\{Languages};

class Diff extends Controller
{
    public function makeData(): array
    {
        [$namespace, $title] = self::parseTitle($this->uri_data->title);
        $uuid = Document::getUuid($namespace, $title, $backlinkrefreshed);
        $error = [];

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $error);
        $ACL->check('read');
        $page = [
            'view_name' => 'diff',
            'title' => $this->uri_data->title,
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                    'forceShowNamespace' => self::forceShowNamespace($namespace, $title)
                ],
                'oldrev' => null,
                'rev' => null,
                'diff' => null
            ],
            'menus' => [],
            'customData' => []
        ];

        if ($error['code'] == 'permission_read'){
            return [
                'view_name' => 'error',
                'title' => Languages::get('page')['error'],
                'data' => $error
            ];
        }

        if (!$uuid) {
            return [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => ['code' => 'no_such_revision']
            ];
        }

        $target_uuid = $_GET['uuid'];
        $new = Document::load($uuid, $target_uuid);

        if(!$new){
            $error = ['code' => 'no_such_revision'];
            return $page;
        }
        
        $old_uuid = $_GET['olduuid'] ?? History::getPrevUuid($uuid, $new['rev']);
        $old = Document::load($uuid, $old_uuid);

        $page['data']['old_uuid'] = $old_uuid;
        $page['data']['rev_uuid'] = $target_uuid ?? $new['uuid'];
        $page['data']['diff'] = self::loadDiff($old['content'], $new['content'], 'r'.$old['rev'].' vs r'.$new['rev']);
        //'debug' => $this->uri_data
        return $page;
    }
}