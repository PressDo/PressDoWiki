<?php
namespace PressDo;
use \stdClass as stdClass;
use PressDo\WikiParams as WP;

/**
 * Wiki Main Class
 */
class WikiCore
{
    public $session, $api_config, $latte, $skin, $dataset, $server, $uri_data, $post, $error, $page, $alert;

    public function __construct()
    {
        require 'vendor/autoload.php';
        require 'helpers/Lang.php';
        require 'helpers/Namespaces.php';

        $this->session = (object) [
            'menus' => [],
            'member' => null,
            'ip' => self::get_ip(),
            'ua' => $_SERVER['HTTP_USER_AGENT']
        ];

        $this->api_config = [
            'force_recaptcha_public' => Config::get('force_recaptcha_public'),
            'recaptcha_public' => Config::get('recaptcha_public'),
            'edit_agree_text' => Config::get('edit_agree_text'),
            'frontpage' => Config::get('frontpage'),
            'sitename' => Config::get('sitename'),
            'copyright_url' => Config::get('copyright_url'),
            'cannonical_url' => Config::get('cannonical_url'),
            'copyright_text' => Config::get('copyright_text'),
            'site_notice' => Config::get('site_notice'),
            'logo_url' => Config::get('logo_url')
        ];

        $this->fe_init();
    }

    /**
     * Initialize Frontend.
     */
    private function fe_init() : void
    {
        $this->latte = new \Latte\Engine;
        $this->latte->setTempDirectory('temp');
        $this->latte->addFilter('formatTime', fn($time) => self::formatTime($time));

        $this->skin = new stdClass;
        $this->skin->name = isset($this->session->member->settings->skin)? $this->session->member->settings->skin_name : Config::get('default_skin');
        $this->skin->config = json_decode(file_get_contents('skins/'.$this->skin->name.'/config.json'), true);
    }

    /**
     * Render document content with syntax
     * 
     * @param string $content   document content
     * @param array $options    renderer options
     * @return array            array(HTML, categories)
     */
    public static function readSyntax($content, string $mark, array $options=[])
    {
        require 'mark/'.$mark.'/loader.php';
        return loadMarkUp($content, $options);
    }

    /**
     * Handle errors.
     * @param string $error_type
     */
    public static function show_error($error_type)
    {
        switch($error_type){
            case 'DBCONNECT':
                die('Cannot connect to database.');
                break;
            case 'ClassNotFound':
                die('Class Not Found');
                break;
        }
    }

    /**
     * Returns HTML.
     * @return HTML
     */
    public function get_page() : string
    {
        $paramSet = [
            'wiki' => $this->dataset, 
            'config' => Config::all(),
            'namespace' => Namespaces::all(), 
            'lang' => Lang::all(),
            'skinConfig' => $this->skin->config,
            'skinName' => $this->skin->name,
            'uri_data' => (array) $this->uri_data,
            'request_uri' => $this->server->REQUEST_URI,
            'post' => (array) $this->post,
            'error' => (array) $this->error,
            'alert' => (array) $this->alert
        ];

        if($this->dataset['page']['view_name'] == 'error'){
            $innerLayout = $this->latte->renderToString('views/layouts/'.$this->dataset['page']['view_name'].'.latte', $paramSet);
        }else{
            switch($this->uri_data->page){
                case 'admin':
                case 'member':
                    $innerLayout = $this->latte->renderToString('views/layouts/'.$this->uri_data->page.'/'.$this->uri_data->title.'.latte', $paramSet);
                    break;
                default:
                    $innerLayout = $this->latte->renderToString('views/layouts/'.$this->dataset['page']['view_name'].'.latte', $paramSet);
            }
        }

        
        $paramSet['innerLayout'] = $innerLayout;
        $body = $this->latte->renderToString('skins/'.$this->skin->name.'/layout.latte', $paramSet);
        $paramSet['body'] = $body;
        
        return $this->latte->renderToString('views/frame.latte', $paramSet);
    }

    /**
     * Initialize Page.
     */
    public function make_page() : void
    {

        $this->dataset = [
            'config' => $this->api_config,
            //'local_config' => $local_config,
            'page' => $this->page,
            'session' => json_decode(json_encode($this->session), true)
        ];
    }

    public static function make_error($msg) : array
    {
        $page = [
            'view_name' => 'error',
            'full_title' => Lang::get('page')['error'],
            'message' => Lang::get('msg')[$msg],
            'data' => [],
            'menus' => [],
            'customData' => []
        ];
        return $page;
    }

    /**
     * Parse namespace and title in full title.
     * 
     * @param string $title     Full title of document
     * @return array            array(raw namespace, Namespace, Title)
     */
    public static function parse_title(string $title) : array
    {
        $_ns = Namespaces::all();
        preg_match('/^('.implode('|',$_ns).'):(.*)$/', $title, $get_ns);
        if(!$get_ns)
            return ['document', $_ns['document'], $title];
        else
            return [array_search($get_ns[1], $_ns), $get_ns[1], $get_ns[2]];
    }

    /**
     * Parse namespace and title in full title.
     * 
     * @param string $rawNS     raw namespace of document
     * @param string $title     title of document
     * @return string           formed title
     */
    public static function make_title(string $rawns, string $title): string
    {
        global $lang, $_ns, $conf;
        if($rawns == 'document' && $conf['UseShortURI'] === false)
            return $title;
        else 
            return $lang['ns:'.$rawns].$title;
    }

    /**
     * Get IP of user.
     */
    public static function get_ip() : string
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        elseif (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        elseif (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        elseif (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        elseif (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        elseif (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = '0.0.0.0';
        
        return $ipaddress;
    }

    public static function formatTime(int $sec): array  
    {
        $week = floor($sec / 604800);
        $sec -= $week * 604800;
        $day = floor($sec / 86400);
        $sec -= $day * 86400;
        $hour = floor($sec / 3600);
        $sec -= $hour * 3600;
        $min = floor($sec / 60);
        $sec -= $min * 60;
        return ['week' => $week, 'day' => $day, 'hour' => $hour, 'minute' => $min, 'second' => $sec];
    }

    /**
     * Generate random string
     * @param int $len  length of string
     * @param bool $u   if use uppercase string
     * @param string $add additional string included to generated one.
     * @return string   generated string
     */
    public static function rand(int $len=16, bool $u=false, string $add='') : string
    {
        $c = '0123456789abcdefghijklmnopqrstuvwxyz';
        if($u) $c .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if(strlen($add) > 0) $c .= $add;
        
        $cl = strlen($c);
        $s = '';
        for ($i=0; $i<$len; $i++) 
            $s .= $c[rand(0, $cl-1)];
        
        return $s;
    }

    public static function ipv62long(string $ip) : string
    {
        $pton = inet_pton($ip);
            $number = '';
            foreach (unpack('C*', $pton) as $byte)
                $number .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
            
            return base_convert(ltrim($number, '0'), 2, 10);
    }

    /**
     * get country of location
     */
    public static function geoip(string $ip): string
    {
        return json_decode(file_get_contents('http://ip-api.com/json/'.$ip), true)['countryCode']; 
    }
}