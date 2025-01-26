<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/blank.php';
require 'controllers/lib/libacl.php';

use PressDo\{Models,WikiACL};
class WikiPage extends WikiCore
{
    public function make_data()
    {
        [$namespace, $title] = self::parse_title($this->uri_data->title);
        $uuid = Models::get_doc_uuid($namespace, $title, $backlinkrefreshed);

        $ACL = new WikiACL($namespace, $title, $uuid, $this->session, $this->error);
        $ACL->check('read');
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

        if($uuid !== false){
            if(!$this->uri_data->query->uuid){
                $this->error = (object) ['code' => 'no_such_revision'];
                return $page;
            }
            $target_uuid = $this->uri_data->query->uuid;
            $new = Models::load($uuid, $target_uuid);
            
            $old_uuid = $this->uri_data->query->olduuid ?? Models::get_before_uuid($uuid, $new['rev']);
            $old = Models::load($uuid, $old_uuid);

            $page['data']['old_uuid'] = $old_uuid;
            $page['data']['rev_uuid'] = $target_uuid;
            $page['data']['diff'] = self::load_diff($old['content'], $new['content'], $old['rev'], $new['rev']);
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