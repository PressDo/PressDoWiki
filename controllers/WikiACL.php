<?php
namespace PressDo;
use PressDo\Models;
class WikiACL 
{
    /**
     * Sets of allowed user in acl
     */
    public static array $allow_list = [];
    public static array $acc_perms, $doc_perms;

    /**
     * if user passes acl setting 
     * value: deny, allow, null
     */
    public static string|null $status = null;
    public static $message = '';

    /**
     * ID of target document
     */
    public static $docid = null;

    public string|null $username;
    public string $ip, $geoip;

    /**
     * Initialize ACL object
     *
     * @param string $namespace     namespace of document
     * @param string $title     title of document without namespace
     * @param string $access    type of access
     * @param object $session   session object
     * @param object|null $error     error object
     */
    public function __construct(public string $namespace, public string $title, public string $access, public object $session, public object|null $error)
    {
        $this->username = $session->member ? $session->member->username : null;
        $this->ip = $session->ip;
        $this->geoip = WikiCore::geoip($this->ip);
    }

    /**
     * Check if user passes ACL settings
     *
     * @return
     */
    public function check()
    {
        self::$docid = Models::get_doc_id($this->namespace, $this->title);

        // fetch acl settings of action
        $acl_doc = Models::fetch_doc_acl(self::$docid, $this->access);
        $acl_ns = Models::fetch_ns_acl($this->namespace, $this->access);

        if(count($acl_doc) > 0){
            $this->scan($acl_doc);
        }
        if(count($acl_ns) > 0){
            $this->scan($acl_ns);
        }

        // undefined allowed people in acl
        if(self::$status === null && count(self::$allow_list) < 1){
            self::$status = 'deny';
            $this->error = (object) [
                'code' => 'permission_'.$this->access,
                'message' => Lang::get('msg')['aclerr_no_rules'],
                'errbox' => false 
            ];
        }elseif(self::$status === 'deny'){
            $error = $this->error;
            $error->message = str_replace(
                ['@1@', '@2@', '@3@', '@4@', '@5@', '@6@', '@7@'], 
                [$this->access, $this->title, implode(' OR ', self::$allow_list), $error->target->id, $error->target->until, $error->target->reason, self::format_cond($error->cond)], 
                Lang::get('msg')[$error->message]
            );
        }
    }

    private function scan(array $acls): void
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
    }

    /**
     * handle action of each ACL settings
     * 
     * @param bool|array $in_cond     if user meets condition / aclgroup data
     * @param string $cond      condition
     * @param string $action    allow, deny or gotons
     */
    private function handle_action(bool|array $in_cond, string $cond, string $action)
    {
        // set status only when status is not set and user meets condition
        if(self::$status === null && $in_cond !== false){
            self::$status = $action;
        }

        switch($action) {
            case 'allow':
                array_push(self::$allow_list, $cond);
                break;
            case 'deny':
                $this->error = (object) [
                    'code' => 'permission_'.$this->access,
                    'message' => 'aclerr_in_target',
                    'cond' => $cond,
                    'errbox' => false
                ];
                if(is_array($in_cond)){
                    $this->error->message = 'aclerr_in_aclgroup';
                    $this->error->target = (object) $in_cond;
                }
                break;
            case 'gotons':
                break;
        }
    }

    /**
     * Check if user has such permission
     * 
     * @param string $perm      target perm of check
     * @param object $session   session object
     * @param string $title     title without namespace
     */
    public static function check_perms(string $perm, object $session, string $title='') : bool
    {
        // get permission data of user
        if(!isset(self::$acc_perms) || !isset(self::$doc_perms)){
            if($session->member?->username !== null)
                self::$acc_perms = Models::get_account_perms($session->member?->username);
            else
                self::$acc_perms = ['ip'];
            
            if (strlen($title) > 0)
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

    private static function format_cond($cond)
    {

    }
}