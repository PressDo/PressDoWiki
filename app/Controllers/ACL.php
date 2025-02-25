<?php
namespace PressDo\app\Controllers;

use PressDo\app\Models\{ACL as ACLModels, Member};
use PressDo\app\Core\Controller;
use PressDo\app\Helpers\{Languages,GeoIP};

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
            ACLModels::getAccountPerms($this->session['uuid'], $this->session['member']['username'], $this->perms);
        else
            array_push($this->perms, 'ip');

        $this->geoip = GeoIP::get($this->session['ip']);
        $this->aclgroups = ACLModels::getUserAclgroups($this->session['ip'], $this->session['uuid'] ?? null);
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
                $msg = sprintf(Languages::get('msg')['aclerr_no_rules'], $access, $full_title);
            else
                $msg = sprintf(Languages::get('msg')['aclerr_not_target'], $access, $full_title, $allowed);

            $this->error = [
                'code' => 'permission_'.$this->access,
                'message' => $msg,
                'errbox' => !($this->access == 'read')
            ];
        } elseif ($this->status === 'deny') {
            $this->error['message'] = sprintf(
                Languages::get('msg')[$this->error['message']],
                $access, $full_title, $allowed, $this->error['target']['id'], $this->error['target']['until'], $this->error['target']['comment'], self::formatCondition($this->error['cond']), 
            );
            $this->error['errbox'] = true;
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
                    $this->handleAction(($this->session['member']['username'] === Member::lookup($cond[1])), $cond[0], $cond[1], $acl['action']);
                    break;
                case 'ip':
                    $this->handleAction(($this->session['ip'] === $cond[1]), $cond[0], $cond[1], $acl['action']);
                    break;
                case 'geoip':
                    $this->handleAction(($this->geoip === $cond[1]), $cond[0], $cond[1], $acl['action']);
                    break;
                case 'aclgroup':
                    //var_dump($)
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
                $input_cond = self::formatCondition($cond.':'.$value);
                array_push($this->allow_list, $input_cond);

                if ($this->status === null && $in_cond)
                    $this->status = $action;
                break;
            case 'deny':
                // apply only when user is in condition and status is unset
                if ($this->status === null && $in_cond) {
                    $this->error = [
                        'code' => 'permission_'.$this->access,
                        'message' => 'aclerr_in_target',
                        'cond' => self::formatCondition($cond.':'.$value),
                        'errbox' => false
                    ];
                    if ($cond == 'aclgroup') {
                        if ($this->aclgroups[$value][0]['until'] === 0)
                            $this->aclgroups[$value][0]['until'] = Languages::get('acl', 'forever_aclgroup');
                        else
                            $this->aclgroups[$value][0]['until'] = date('Y-m-d H:i:s', $this->aclgroups[$value][0]['until']);

                        $this->error['message'] = 'aclerr_in_aclgroup';
                        $this->error['target'] = $this->aclgroups[$value][0];
                    }
                }
                if ($this->status === null && $in_cond)
                    $this->status = $action;
                break;
            case 'gotons':
            if ($this->status === null && $in_cond)
                $this->status = $action;
                break;
        }
    }

    private static function formatCondition(string $cond): string
    {
        // not built
        $con = explode(':', $cond);

        // return 하므로 break가 불요
        switch ($con[0]) {
            case 'aclgroup':
                return Languages::get('acl', 'aclgroup').' '.$con[1];
            case 'perm':
                return Languages::get('perm', $con[1]) ?? $cond;
            case 'user':
                return Languages::get('acl', 'specific_user');
            default:
                return $cond;
        }
    }
}