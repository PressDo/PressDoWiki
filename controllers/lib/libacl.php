<?php
namespace PressDo;

require 'models/lib/libacl.php';
use PressDo\ACLModels;

class WikiACL
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
    * @param object $session   session object
    * @param object|null $error     error object
    */
    public function __construct(private string $namespace, private string $title, private string|bool $uuid, private object $session, public object|null &$error)
    {
        if($session->member)
            ACLModels::get_account_perms($session->member, $this->perms);
        else
            array_push($this->perms, 'ip');

        $this->geoip = WikiCore::geoip($session->ip);

        $this->aclgroups = ACLModels::get_user_aclgroups($session);
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
        $acl_doc = ACLModels::fetch_doc_acl($this->uuid, $this->access);
        $acl_ns = ACLModels::fetch_ns_acl($this->namespace, $this->access);

        if(count($acl_doc) > 0){
            $this->scan($acl_doc);
        }
        if(count($acl_ns) > 0){
            $this->scan($acl_ns);
        }
        
        $access = Lang::get('acl')[$this->access];
        $allowed = implode(' OR ', $this->allow_list);
        $full_title = WikiPage::make_title($this->namespace, $this->title);

        // undefined allowed people in acl
        if($this->status === null){
            $this->status = 'deny';
            if(count($this->allow_list) < 1)
                $msg = str_replace(['@1@', '@t@'], [$access, $full_title], Lang::get('msg')['aclerr_no_rules']);
            else
                $msg = str_replace(['@1@', '@3@', '@t@'], [$access, $allowed, $full_title], Lang::get('msg')['aclerr_not_target']);

            $this->error = (object) [
                'code' => 'permission_'.$this->access,
                'message' => $msg,
                'errbox' => ($this->access == 'read'? false: true)
            ];
        }elseif($this->status === 'deny'){
            $error = $this->error;
            $error->message = str_replace(
                ['@1@', '@3@', '@4@', '@5@', '@6@', '@7@', '@t@'], 
                [$access, $allowed, $error->target->id, $error->target->until, $error->target->reason, self::format_cond($error->cond), $full_title], 
                Lang::get('msg')[$error->message]
            );
            $error->errbox = true;
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
                    $this->handle_action(in_array($cond[1], $this->perms), $cond[0], $cond[1], $acl['action']);
                    break;
                case 'member':
                    $this->handle_action(($this->session->member->username === $cond[1]), $cond[0], $cond[1], $acl['action']);
                    break;
                case 'ip':
                    $this->handle_action(($this->session->ip === $cond[1]), $cond[0], $cond[1], $acl['action']);
                    break;
                case 'geoip':
                    $this->handle_action(($this->geoip === $cond[1]), $cond[0], $cond[1], $acl['action']);
                    break;
                case 'aclgroup':
                    $this->handle_action(in_array($cond[1], array_keys($this->aclgroups)), $cond[0], $cond[1], $acl['action']);
                    break;
            }

            if($this->status == 'allow')
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
    private function handle_action(bool $in_cond, string $cond, string $value, string $action)
    {
        switch($action) {
            case 'allow':
                if($cond == 'user')
                    $input_cond = Lang::get('acl')['specific_user'];
                else
                    $input_cond = $cond.':'.$value;
                
                array_push($this->allow_list, $input_cond);
                break;
            case 'deny':
                // apply only when user is in condition and status is unset
                if($this->status === null && $in_cond){
                    $this->error = (object) [
                        'code' => 'permission_'.$this->access,
                        'message' => 'aclerr_in_target',
                        'cond' => $cond.':'.$value,
                        'errbox' => false
                    ];
                    if($cond == 'aclgroup'){
                        $this->error->message = 'aclerr_in_aclgroup';
                        $this->error->target = [$value => $this->aclgroups[$value][0]['id']];
                    }
                }
                break;
            case 'gotons':
                break;
        }
        // set status only when status is not set and user meets condition
        if($this->status === null && $in_cond)
            $this->status = $action;
    }

    /**
        * Check if user has such permission (Deprecated)
        * 
        * @param string $perm      target perm of check
        * @param object $session   session object
        * @param string $title     title without namespace
        */
    public function check_perms(string $perm, object $session, string $title='') : bool
    {
        // get permission data of user
        if(!isset($this->acc_perms) || !isset($this->doc_perms)){
            if($session->member?->username !== null)
                $this->acc_perms = ACLModels::get_account_perms($session->member?->username);
            else
                $this->acc_perms = ['ip'];
            
            if (strlen($title) > 0)
                $this->doc_perms = ACLModels::get_document_perms($this->uuid, $session);
        }

        if($session->username !== null){
            $perms = array_merge($this->acc_perms, $this->doc_perms, ['member']);
            if($title == $session->member?->username)
                $perms = array_merge($this->acc_perms, $this->doc_perms, ['match_username_and_document_title']);
        }else
            $perms = array_merge($this->acc_perms, $this->doc_perms, ['member']);
        
        if(in_array($perm, $perms))
            return true;
        else
            return false;
    }

    private function format_cond($cond)
    {

    }
}