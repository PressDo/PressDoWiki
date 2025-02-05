<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{BlockHistory as BH, Member, ACL};
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

        $keyword = $_GET['query'] ?? '';


        $data = array_reverse(BH::get($type, $keyword, $from, $until));
        $dataset = [];
        $aclgroups = ACL::aclgroups();
        foreach ($data as $d){
            $ip = $member = $Cuuid = $target_ip = $target_member = $uuid2 = $aclgroup = null;

            if ($d['until'] === null)
                $dur = null;
            else
                $dur = $d['until'] == '0' ? 'forever' : self::formatTime($d['until'] - $d['datetime']);
            
            if ($d['executor_i'] !== null) {
                $Cuuid = BH::bin2uuid($d['executor_i']);
                $ip = Member::ipLookup($Cuuid);
                $member = null;
            } elseif($d['executor_m'] !== null) {
                $Cuuid = BH::bin2uuid($d['executor_m']);
                $member = Member::lookup($Cuuid);
                $ip = null;
            }

            if ($d['target_ip'] !== null) {
                $target_ip = inet_ntop($d['target_ip']).'/'.$d['mask'];
            }

            if ($d['target_member'] !== null) {
                $uuid2 = BH::bin2uuid($d['target_member']);
                $target_member = Member::lookup($uuid2);
            }

            if ($d['target_aclgroup']) {
                $aclgroup = $aclgroups[$d['target_aclgroup']];
            }
            
            array_push($dataset, [
                'author' => $member,
                'author_ip' => $ip,
                'author_uuid' => $Cuuid,
                'datetime' => $d['datetime'],
                'action' => $d['action'],
                'content' => [
                    'id' => $d['id'],
                    'ip' => $target_ip,
                    'duration' => $dur,
                    'memo' => $d['comment'],
                    'member' => $target_member,
                    'member_uuid' => $uuid2,
                    'aclgroup' => $aclgroup,
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
        [$max, $min] = BH::get_block_history_count($type,$keyword);
        if ($page['data']['history'][0]['content']['id'] !== $max)
            $page['data']['prev_page'] = $page['data']['history'][0]['content']['id'] + 1;
        if (end($page['data']['history'])['content']['id'] !== $min)
            $page['data']['next_page'] = end($page['data']['history'])['content']['id'] - 1;

        return $page;
    }
}