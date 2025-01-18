<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/backlink.php';
require 'controllers/WikiACL.php';

use PressDo\Models;
use PressDo\WikiACL;
class WikiPage extends WikiCore
{
    public function make_data(): array
    {
        list($namespace, $title) = self::parse_title($this->uri_data->title);

        $ACL = new WikiACL($namespace, $title, 'read', $this->session, $this->error);
        $ACL->check();
        $page = [
            'view_name' => 'backlink',
            'title' => $this->uri_data->title,
            'subtitle' => '',
            'data' => [
                'document' => [
                    'namespace' => $namespace,
                    'title' => $title,
                ],
                'backlink_count' => [],
                'backlink' => []
            ],
            'menus' => [],
            'customData' => []
        ];

        if ($this->error->code == 'permission_read'){
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => (array) $this->error
            ];
            return $page;
        }

        if(Models::exist($namespace,$title)){
            $uuid = Models::get_doc_uuid($namespace,$title);
            $page['subtitle'] .= Lang::get('page')['backlink'];
            if(isset($_GET['flag']) && in_array(intval($_GET['flag']), [0, 1, 2, 4, 8])){
                $flag = [
                    0 => null,
                    1 => 'link',
                    2 => 'file',
                    4 => 'include',
                    8 => 'redirect'
                ];
                $type = $flag[intval($_GET['flag'])];
            }else{
                $type = null;
            }

            $bl_count = Models::count_backlink($namespace, $title);
            
            foreach($bl_count as $b){
                array_push($page['data']['backlink_count'], ['namespace' => $b['namespace'], 'count' => $b['cnt']]);
            }

            if(isset($_GET['namespace']) && in_array(intval($_GET['namespace']), Namespaces::all())){
                $target_ns = $_GET['namespace'];
            }else{
                $target_ns = $bl_count[0]['namespace'];
            }
            if($target_ns !== null){
                $backlinks = Models::get_backlink($namespace, $title, $target_ns, $type);
                ksort($backlinks);
                foreach($backlinks as $title => $b){
                    // backlink 정렬
                    $firstchar = iconv_substr($title, 0, 1);
                    if(self::is_hangeul($firstchar))
                        $head = self::ko_head($firstchar);
                    else
                        $head = $firstchar;

                    if(!isset($page['data']['backlink'][$head]))
                        $page['data']['backlink'][$head] = [];
                    
                    array_push($page['data']['backlink'][$head], ['document' => ['namespace' => $b[0]['namespace'], 'title' => $title, 'force_show_namespace' => Config::get('force_show_namespace')], 'type' => $b[0]['type']]);
                }
            }
        }else{
            $this->error = (object) ['code' => 'no_such_document'];
            $page = [
                'view_name' => 'error',
                'title' => Lang::get('page')['error'],
                'data' => (array) $this->error
            ];
        }
        return $page;
    }

    private static function utf8_ord($c)
    {
        $len = strlen($c);
        if($len <= 0) return false;
        $h = ord($c[0]);
        if ($h <= 0x7F) return $h;
        if ($h < 0xC2) return false;
        if ($h <= 0xDF && $len>1) return ($h & 0x1F) <<  6 | (ord($c[1]) & 0x3F);
        if ($h <= 0xEF && $len>2) return ($h & 0x0F) << 12 | (ord($c[1]) & 0x3F) <<  6 | (ord($c[2]) & 0x3F);		  
        if ($h <= 0xF4 && $len>3) return ($h & 0x0F) << 18 | (ord($c[1]) & 0x3F) << 12 | (ord($c[2]) & 0x3F) << 6 | (ord($c[3]) & 0x3F);
        return false;
    }
    
    private static function is_hangeul(string $c)
    {
        $o = self::utf8_ord($c);
        if( 0x1100<=$o && $o<=0x11FF ) return true;
        if( 0x3130<=$o && $o<=0x318F ) return true;
        if( 0xAC00<=$o && $o<=0xD7A3 ) return true;
        return false;
    }

    private static function ko_head(string $char)
    {
        $heads = ['ㄱ','ㄲ','ㄴ','ㄷ','ㄸ','ㄹ','ㅁ','ㅂ','ㅃ','ㅅ','ㅆ','ㅇ','ㅈ','ㅉ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ'];
        $code = self::utf8_ord($char) - 44032;
        if ($code > -1 && $code < 11172) {
            $result = $heads[$code / 588];
        }elseif(in_array($char, $heads)) {
            $result = $char;
        }
        return $result;
    }
}