<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Document,BlockHistory as BH};
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\{Namespaces,Languages,Config};

class BlockHistory extends Controller
{
    public function makeData(): array
    {
        if (!empty($_GET['from']) && is_integer($_GET['from']))
            $from = $_GET['from'];

        if (!empty($_GET['until']) && is_integer($_GET['until']))
            $until = $_GET['until'];
        else
            $until = 0;

        if ($_GET['target'] == 'author')
            $type = 'author';
        else
            $type = 'text';

        $keyword = $_GETquery ?? '';


        $data = array_reverse(BH::get($type, $keyword, $from, $until));
        $dataset = [];
        foreach ($data as $d){
            if ($d['until'] === null)
                $dur = null;
            else
                $dur = $d['until'] == '0' ? 'forever' : self::formatTime($d['until'] - $d['datetime']);
            
            $exc = explode(':',$d['executor']);
            if($exc[0] == 'm'){
                $exec = $exc[1];
                $exec_ip = null;
            }else{
                $exec = null;
                $exec_ip = $exc[1];
            }
            array_push($dataset, [
                'author' => $exec,
                'author_ip' => $exec_ip,
                'datetime' => $d['datetime'],
                'action' => $d['action'],
                'content' => [
                    'id' => $d['id'],
                    'ip' => $d['display_ip'],
                    'duration' => $dur,
                    'memo' => $d['comment'],
                    'member' => $d['target_member'],
                    'aclgroup' => $d['target_aclgroup'],
                    'target_id' => $d['target_id'],
                    'granted' => $d['granted']
                ]
            ]);
        }
        
        $page = [
            'view_name' => 'BlockHistory',
            'title' => Languages::get('page')['BlockHistory'],
            'data' => [
                'prev_page' => null,
                'next_page' => null,
                'history' => $dataset
            ]
        ];

        // 첫 항목이 (조건 내에서) 최신 항목이 아닌 경우 띄움
        [$max, $min] = Models::get_block_history_count($type,$keyword);
        if ($page['data']['history'][0]['content']['id'] !== $max)
            $page['data']['prev_page'] = $page['data']['history'][0]['content']['id'] + 1;
        if (end($page['data']['history'])['content']['id'] !== $min)
            $page['data']['next_page'] = end($page['data']['history'])['content']['id'] - 1;
        return $page;
    }
}