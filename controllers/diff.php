<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/blank.php';
require 'controllers/WikiACL.php';

use PressDo\Models;
use PressDo\WikiACL;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        list($rawns, $namespace, $title) = self::parse_title($this->uri_data->title);

        $ACL = new WikiACL($rawns, $title, 'read', $this->session, $this->error);
        $ACL->check();
        $page = [
            'view_name' => 'diff',
            'title' => $this->uri_data->title,
            'subtitle' => Lang::get('page')['diff'],
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                ],
                'oldrev' => null,
                'rev' => null,
                'diff' => null
            ],
            'menus' => [],
            'customData' => []
        ];

        if ($this->error->code == 'permission_read'){
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => $this->error
            ];
            return $page;
        }

        if(Models::exist($rawns,$title)){
            $lver = Models::get_version($rawns,$title);
            $rev = $this->uri_data->query->rev;
            $oldrev = $this->uri_data->query->oldrev;

            if(!$this->uri_data->query->rev || !$this->uri_data->query->oldrev || !is_numeric($rev) || !is_numeric($oldrev) || $rev > $lver || $oldrev < 1 || $rev <= $oldrev){
                $this->error->code = 'no_such_revision';
                return $page;
            }

            $old = Models::load($rawns, $title, $oldrev)['content'];
            $new = Models::load($rawns, $title, $rev)['content'];

            $page['data']['oldrev'] = $oldrev;
            $page['data']['rev'] = $rev;
            $page['data']['diff'] = self::load_diff($old, $new, $oldrev, $rev);
            //'debug' => $this->uri_data
            return $page;
        }else{
            $this->error = (object) ['code' => 'no_such_revision'];
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => (array) $this->error
            ];
            return $page;
        }
    }

    private static function load_diff(string $old, string $new, int $ov, int $nv): string
    {
        require 'external/diff/Diff.php';
        require 'external/diff/Inline.php';

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