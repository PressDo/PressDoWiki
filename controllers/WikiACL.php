<?php
namespace PressDo;
use PressDo\Models;
class WikiACL 
{
    /**
     * Sets of allowed user in acl
     */
    public static array $allowed = [];
    public static array $acc_perms, $doc_perms = [];

    /**
     * if user passes acl setting 
     */
    public static $status = null;
    public static $message = '';

    /**
     * ID of target document
     */
    public static $docid = null;

    /**
     * if user is in ACL condition
     */
    public static $user_in = null;

    /**
     * Initialize ACL object
     *
     * @param string $rawns     RAW namespace of document
     * @param string $title     title of document without namespace
     * @param string $access    type of access
     * @param object $session   session object
     * @param object|null $error     error object
     */
    public function __construct(string $rawns, string $title, string $access, object $session, object|null $error)
    {
        $this->namespace = $rawns;
        $this->title = $title;
        $this->access = $access;
        
        $this->session = $session;
        $this->username = $session->member ? $session->member->username : null;
        $this->ip = $session->ip;
        $this->geoip = WikiCore::geoip($this->ip);

        $this->error = $error;
        
    }

    /**
     * Check if user passes ACL settings
     *
     * @return
     */
    public function check()
    {
        /*
        structure
        array(4) {
            [0]=> array(5) { 
                ["id"]=> int(14) 
                ["access"]=> string(4) "read" 
                ["condition"]=> string(12) "member:admin" 
                ["action"]=> string(5) "allow" 
                ["expired"]=> int(0) 
            } [1]=> array(5) { 
                ["id"]=> int(21) 
                ["access"]=> string(4) "edit" ["condition"]=> string(13) "aclgroup:TEST" ["action"]=> string(4) "deny" ["expired"]=> int(0) } [2]=> array(5) { ["id"]=> int(28) ["access"]=> string(4) "read" ["condition"]=> string(17) "ip:119.194.133.12" ["action"]=> string(5) "allow" ["expired"]=> int(0) } [3]=> array(5) { ["id"]=> int(34) ["access"]=> string(4) "read" ["condition"]=> string(23) "aclgroup:PressDo-tester" ["action"]=> string(5) "allow" ["expired"]=> int(0) } } string(8) "document" string(29) "문법 테스트/나무마크" array(0) { }
         */

        $acl_doc = Models::fetch_doc_acl(Models::get_doc_id($this->namespace, $this->title), $this->access);
        $acl_ns = Models::fetch_ns_acl($this->namespace, $this->access);

        self::$docid = Models::get_doc_id($this->namespace, $this->title);

        self::$acc_perms = self::$doc_perms = [];
        self::$status = self::$user_in = null;
        $this->scan($acl_doc);

        // no rules in document ACL or gotons
        if(self::$status === 'gotons' || count($acl_doc) < 1){
            // scan namespace
            self::$status = null;
            $this->scan($acl_ns);
        }
        
        // return error if denied
        if(self::$status === 'deny'){
            $error = $this->error;
            $error->message = str_replace(
                ['@1@', '@2@', '@3@', '@4@', '@5@', '@6@', '@7@'], 
                [$this->access, $this->title, implode(' OR ', self::$allowed), $aclgroupid, $until, $reason, self::format_cond($cond)], 
                $error->message
            );
        }
    }

    /**
     * Check if user has such permission
     * 
     * @param string $perm      target perm of check
     * @param object $session   session object
     * @param string $title     title without namespace
     */
    public static function check_perms(string $perm, object $session, string $title) : bool
    {
        // get permission data of user
        if(!self::$acc_perms || !self::$doc_perms){
            if($session->member?->username !== null)
                self::$acc_perms = Models::get_account_perms($session->member?->username);
            else
                self::$acc_perms = ['ip'];
            self::$doc_perms = Models::get_document_perms(self::$docid, $session);
        }

        if($session->username !== null){
            $perms = array_merge(self::$acc_perms, self::$doc_perms, ['member']);
            if($title == $session->member?->username)
                $perms = array_merge(self::$acc_perms, self::$doc_perms, ['match_username_and_document_title']);
        }else
            $perms = array_merge(self::$acc_perms, self::$doc_perms, ['member']);
        
        if(in_array($perm, $perms))
            return true;
        else
            return false;
    }

    private function scan(array $acls, bool $ns = false) : void
    {
        // into each rule
        foreach($acls as $acl) {
            $cond = explode(':', $acl['condition']);
            switch($cond[0]) {
                case 'perm':
                    $this->handle_action(self::check_perms($cond[1], $this->session, $this->title), $acl['condition'], $acl['action']);
                    break;
                case 'member':
                    $this->handle_action(($this->username === $cond[1]), $acl['condition'], $acl['action']);
                    break;
                case 'ip':
                    $this->handle_action(($this->ip === $cond[1]), $acl['condition'], $acl['action']);
                    break;
                case 'geoip':
                    $this->handle_action(($this->geoip === $cond[1]), $acl['condition'], $acl['action']);
                    break;
                case 'aclgroup':
                    $this->handle_action(Models::in_aclgroup($this->session, $cond[1]), $acl['condition'], $acl['action']);
                    break;
            }
        }
        
        // make gotons when not exist in doc
        if(self::$status === null)
            self::$status = 'gotons';

        // rules not exist in ns
        if($ns && self::$status === null){
            self::$status = 'deny';
            $this->error = (object) [
                'code' => 'permission_'.$this->access,
                'message' => Lang::get('msg')['aclerr_no_rules'],
                'errbox' => false 
            ];
        }
    }

    /**
     * handle action of each ACL settings
     * 
     * @param bool $in_cond     if user meets condition
     * @param string $cond      condition
     * @param string $action    allow, deny or gotons
     */
    private function handle_action(bool $in_cond, string $cond, string $action)
    {
        // set status only when status is not set and user meets condition
        if(self::$status === null && $in_cond === true){
            self::$status = $action;
        }

        switch($action) {
            case 'allow':
                array_push(self::$allowed, $cond);
                break;
            case 'deny':
                $this->error = (object) [
                    'code' => 'permission_'.$this->access,
                    'message' => 'aclerr_in_target',
                    'errbox' => false 
                ];
                break;
            case 'gotons':
                break;
        }
    }
}