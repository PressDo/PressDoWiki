<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Models\{Member,ACL};
use PressDo\App\Core\Controller;
use PressDo\App\Helpers\{Namespaces,Languages,Config};

class Aclgroup extends Controller
{
    public function makeData(): array
    {
        if (isset($_POST['id']))
            $group = ACL::groupIdLookup($_POST['id']);

        $perms = [];
        if (isset($this->session['member']))
            ACL::getAccountPerms($this->session['uuid'], $this->session['member']['username'], $perms);

        
        if (!empty($_POST['name']) && in_array('aclgroup', $perms)) {
            $go = ACL::addACLGroup($_POST['name']);
            if (!$go)
                $errmsg = 'aclgroup_group_exists';
        }

        $aclgroups = ACL::aclgroups();

        if (!empty($_POST['delnm'])
            && in_array($_POST['delnm'], $aclgroups)
            && self::getAllowedAction(array_search($_POST['delnm'], $aclgroups), 'group_remove', $perms)) {
            
            ACL::deleteACLGroup($_POST['delnm']);
            unset($aclgroups[array_search($_POST['delnm'], $aclgroups)]);
        }

        // aclgroup member control
        if (in_array($_POST['group'], $aclgroups)
            && intval($_POST['duration_raw']) >= 0
            && self::getAllowedAction(array_search($_POST['group'], $aclgroups), 'add', $perms)) {

            $errmsg = '';

            if (empty($_POST['note']))
                $errmsg = 'err_required_note';

            // ip만 입력해도 처리
            if (!empty($_POST['ip']) && strpos($_POST['ip'], '/') === false)
                $_POST['ip'] .= '/32';

            if ($_POST['mode'] == 'ip' && !self::validateCIDR($_POST['ip']))
                $errmsg = 'invalid_cidr';
            elseif ($_POST['mode'] == 'username' && !($userdata = Member::exist($_POST['username'])))
                $errmsg = 'invalid_username';
            
            if ($_POST['mode'] == 'username') {
                $uuid = $userdata['uuid'];
                $ip = null;
                $dup = ACL::groupAddDuplicate($uuid, null, $_POST['group']);
            } else {
                $ip = $_POST['ip'];
                $uuid = null;
                $dup = ACL::groupAddDuplicate($uuid, $ip, $_POST['group']);
            }

            if ($dup)
                $errmsg = 'acl_already_exists';

            if (!$this->session['member']) {
                if (!$this->session['uuid']) {
                    $this->session['uuid'] = Member::getIpUuid($this->session['ip']);
                }
                $exec_i = $this->session['uuid'];
                $exec_m = null;
            } else {
                $exec_i = null;
                $exec_m = $this->session['uuid'];
            }

            if ($_POST['duration_raw'] > 0)
                $duration = time() + $_POST['duration_raw'];
            else
                $duration = 0;

            if (empty($errmsg))
                ACL::addtoGroup(
                    $exec_m,
                    $exec_i, 
                    $ip, 
                    $uuid, 
                    $_POST['group'],
                    $_POST['note'],
                    $duration
                );
        } elseif (!empty($group) && self::getAllowedAction(array_search($group, $aclgroups), 'remove', $perms) && !empty($_POST['note'])) {
            if (!$this->session['member']) {
                if (!$this->session['uuid']) {
                    $this->session['uuid'] = Member::getIpUuid($this->session['ip']);
                }
                $exec_i = $this->session['uuid'];
                $exec_m = null;
            } else {
                $exec_i = null;
                $exec_m = $this->session['uuid'];
            }

            ACL::removefromGroup(
                $exec_m,
                $exec_i, 
                $_POST['id'],
                $_POST['note']
            );
        }

        // aclgroup이 없을 경우에 대비
        if (!empty($aclgroups)) {
            $from = $until = null;
            if (!empty($_GET['from']) && is_integer($_GET['from']))
                $from = $_GET['from'];
    
            if (!empty($_GET['until']) && is_integer($_GET['until']))
                $until = $_GET['until'];

            $accessible_group_names = [];

            foreach ($aclgroups as $id => $g) {
                if (!self::getAllowedAction($id, 'access', $perms))
                    continue;

                array_push($accessible_group_names, $g);
            }

            if (!empty($accessible_group_names)) {
                $target_group = in_array($_GET['group'], $accessible_group_names) ? $_GET['group'] : $accessible_group_names[0];
                $group_id = array_search($target_group, $aclgroups);

                $list = ACL::getAclgroupMembers($target_group, $from, $until);
                $len = count($list);

                for ($i = 0; $i < $len; $i++) {
                    if ($list[$i]['target_ip'] !== null)
                        $list[$i]['target_ip'] = inet_ntop($list[$i]['target_ip']).'/'.$list[$i]['mask'];

                    if ($list[$i]['target_member'] !== null)
                        $list[$i]['target_member'] = Member::lookup(Member::bin2uuid($list[$i]['target_member']));

                    unset($list[$i]['mask']);
                }
            }
        }

        if (!empty($errmsg))
            $this->error = self::makeErrorBox($errmsg);

        $page = [
            'view_name' => 'aclgroup',
            'title' => 'ACLGroup',
            'data' => [
                'aclgroups' => $accessible_group_names ?? [],
                'group_addable' => in_array('aclgroup', $perms) || in_array('developer', $perms),
                'group_removable' => self::getAllowedAction($group_id, 'group_remove', $perms),
                'list_addable' => self::getAllowedAction($group_id, 'add', $perms),
                'list_removable' => self::getAllowedAction($group_id, 'remove', $perms),
                'currentgroup' => $target_group,
                'list' => $list ?? []
            ]
        ];

        return $page;
    }

    /**
     * get if such action is allowed
     * @param int $id
     * @param string $action
     * @param array $perms
     * @return bool|null
     */
    private static function getAllowedAction(?int $id, string $action, array $perms): ?bool
    {
        if (!$id)
            return false;
        if ($p = Config::get("aclgroup.$id.$action".'_perm')) {
            return in_array($p, $perms) || in_array('developer', $perms);
        } else {
            if ($action == 'access')
                return true;
            return in_array('aclgroup', $perms) || in_array('developer', $perms);
        }
    }

    private static function validateCIDR(string $CIDR): bool
    {
        if (strpos($CIDR, '/') !== false) {
            [$ip, $mask] = explode('/', $CIDR, 2);
            if (!filter_var($ip, FILTER_VALIDATE_IP))
                return false;
            if (!ctype_digit($mask))
                return false;
            $mask = (int) $mask;
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && ($mask < 0 || $mask > 32))
                return false;
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && ($mask < 0 || $mask > 128))
                return false;
            return true;
        }
        return false;
    }
}