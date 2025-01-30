<?php
namespace PressDo\app\Controllers;

use PressDo\app\Models\ACL as ACLModels;
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\Languages;

class ACL extends Controller
{
    // user's permission and aclgroup
    public array $perms = ['any'];
    public array $aclgroups = [];

    // user's geoip
    public $geoip;

    // user's action
    public $status;
    public $access;
    
    // allowed parties
    public array $allow_list = [];

    /**
     * Initialize ACL object and check member & ip
     * params will be defined in class
     *
     * @param string $namespace     namespace of document
     * @param string $title     title of document without namespace
     * @param string|bool $uuid      uuid of document
     * @param array $error     error object
     */
    public function __construct(private string $namespace, private string $title, private string|bool $uuid, public array $session, public array &$error)
    {
        if ($this->session['member'])
            ACLModels::getAccountPerms($this->session['member'], $this->perms);
        else
            array_push($this->perms, 'ip');

        $this->geoip = parent::geoip($this->session['ip']);
        $this->aclgroups = ACLModels::getUserAclgroups($this->session);
    }

    /**
     * Check if user passes ACL settings
     * @param string $access     type of access
     * @return
     */
    public function check(string $access)
    {
        $this->access = $access;
        $this->status = null;
        $this->allow_list = [];

        // fetch acl settings of action
        $acl_doc = ACLModels::fetchDocACL($this->uuid, $this->access);
        $acl_ns = ACLModels::fetchNSACL($this->namespace, $this->access);

        if (count($acl_doc) > 0) {
            $this->scan($acl_doc);
        }
        if (count($acl_ns) > 0) {
            $this->scan($acl_ns);
        }
        
        $access = Languages::get('acl')[$this->access];
        $allowed = implode(' OR ', $this->allow_list);
        $full_title = parent::makeTitle($this->namespace, $this->title);

        // undefined allowed people in acl
        if ($this->status === null) {
            $this->status = 'deny';
            if (count($this->allow_list) < 1)
                $msg = str_replace(['@1@', '@t@'], [$access, $full_title], Languages::get('msg')['aclerr_no_rules']);
            else
                $msg = str_replace(['@1@', '@3@', '@t@'], [$access, $allowed, $full_title], Languages::get('msg')['aclerr_not_target']);

            $this->error = [
                'code' => 'permission_'.$this->access,
                'message' => $msg,
                'errbox' => !($this->access == 'read')
            ];
        } elseif ($this->status === 'deny') {
            $error = $this->error;
            $error['message'] = str_replace(
                ['@1@', '@3@', '@4@', '@5@', '@6@', '@7@', '@t@'], 
                [$access, $allowed, $error['target']['id'], $error['target']['until'], $error['target']['reason'], self::formatCondition($error['cond']), $full_title], 
                Languages::get('msg')[$error['message']]
            );
            $error['errbox'] = true;
        }
    }

    /**
     * Scan ACL ruleset
     * @param array $acls ruleset array
     * @return void
     */
    private function scan(array $acls): void
    {
        // into each rule
        foreach($acls as $acl) {
            $cond = explode(':', $acl['condition']);
            switch($cond[0]) {
                case 'perm':
                    $this->handleAction(in_array($cond[1], $this->perms), $cond[0], $cond[1], $acl['action']);
                    break;
                case 'member':
                    $this->handleAction(($this->session['member']['username'] === $cond[1]), $cond[0], $cond[1], $acl['action']);
                    break;
                case 'ip':
                    $this->handleAction(($this->session['ip'] === $cond[1]), $cond[0], $cond[1], $acl['action']);
                    break;
                case 'geoip':
                    $this->handleAction(($this->geoip === $cond[1]), $cond[0], $cond[1], $acl['action']);
                    break;
                case 'aclgroup':
                    $this->handleAction(in_array($cond[1], array_keys($this->aclgroups)), $cond[0], $cond[1], $acl['action']);
                    break;
            }

            if ($this->status == 'allow')
                break;
        }
    }

    /**
        * handle action of each ACL settings
        * 
        * @param bool $in_cond     if user meets condition
        * @param string $cond      condition
        * @param string $value
        * @param string $action    allow, deny or gotons
        */
    private function handleAction(bool $in_cond, string $cond, string $value, string $action): void
    {
        switch($action) {
            case 'allow':
                if ($cond == 'user')
                    $input_cond = Languages::get('acl')['specific_user'];
                else
                    $input_cond = $cond.':'.$value;
                
                array_push($this->allow_list, $input_cond);
                break;
            case 'deny':
                // apply only when user is in condition and status is unset
                if ($this->status === null && $in_cond) {
                    $this->error = (object) [
                        'code' => 'permission_'.$this->access,
                        'message' => 'aclerr_in_target',
                        'cond' => $cond.':'.$value,
                        'errbox' => false
                    ];
                    if ($cond == 'aclgroup') {
                        $this->error['message'] = 'aclerr_in_aclgroup';
                        $this->error['target'] = [$value => $this->aclgroups[$value][0]['id']];
                    }
                }
                break;
            case 'gotons':
                break;
        }
        // set status only when status is not set and user meets condition
        if ($this->status === null && $in_cond)
            $this->status = $action;
    }

    private static function formatCondition(string $cond): string
    {
        // not built
        return $cond;
    }
}