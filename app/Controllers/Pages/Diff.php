<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Document,History};
use PressDo\app\Core\Controller;
use PressDo\app\Controllers\ACL as WikiACL;
use PressDo\app\Helpers\{Languages};

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
        $page['data']['diff'] = self::load_diff($old['content'], $new['content'], $old['rev'], $new['rev']);
        //'debug' => $this->uri_data
        return $page;
    }

    private static function load_diff(string $old, string $new, int $ov, int $nv): string
    {
        require '../app/Helpers/Libraries/diff/Diff.php';
        require '../app/Helpers/Libraries/diff/Inline.php';

        $a = explode("\n", $old);
        $b = explode("\n", $new);

        $options = array(
            //'ignoreWhitespace' => true,
            //'ignoreCase' => true,
        );

        $diff = new \Diff($a, $b, $options); 
        $ren = new \Diff_Renderer_Html_Inline;
        $ren->oldrev = $ov;
        $ren->newrev = $nv;
        $ren->linecnt = [count($a),count($b)];

        return $diff->render($ren);
    }
}