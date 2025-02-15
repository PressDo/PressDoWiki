<?php
namespace PressDo\app\Core;

use PressDo\app\Models\Member;
use PressDo\app\Helpers\{Config,Namespaces,Languages};
use PressDo\app\Helpers\Mark\Loader;
use \yidas\socketMailer\Mailer;

class Controller
{
    public object $uri_data;
    public array $error, $session, $api_config, $array, $dataset;
    public $page, $alert;

    public function __construct()
    {
        $this->session = [
            'menus' => [],
            'member' => null,
            'ip' => self::getIPAddr(),
            'ua' => $_SERVER['HTTP_USER_AGENT']
        ];
        $uuid = Member::getIpUuid($this->session['ip'], true);
        if ($uuid !== null)
            $this->session['uuid'] = $uuid;

        $this->api_config = [
            'force_recaptcha_public' => Config::get('wiki.force_recaptcha_public'),
            'recaptcha_public' => Config::get('wiki.recaptcha_public'),
            'editagree_text' => Config::get('wiki.editagree_text'),
            'front_page' => Config::get('wiki.front_page'),
            'site_name' => Config::get('wiki.site_name'),
            'copyright_url' => Config::get('wiki.copyright_url'),
            'cannonical_url' => Config::get('wiki.canonical_url'),
            'copyright_text' => Config::get('wiki.copyright_text'),
            'sitenotice' => Config::get('wiki.sitenotice'),
            'logo_url' => Config::get('wiki.logo_url')
        ];
    }

    /**
     * Render document content with syntax
     * 
     * @param string $content   document content
     * @param string $mark      mark language
     * @param array $options    renderer options
     * @return array            array(HTML, categories)
     */
    protected static function readSyntax(string $content, array $options=[]): array|string
    {
        return Loader::loadMarkUp($content, $options);
    }

    /**
     * Returns HTML.
     * @return string
     */
    private function getPage(): string
    {
        $View = new View();
        $View->renderInit();

        $license = $this->dataset['page']['view_name'] == 'License' ? json_decode(file_get_contents('../config/license.json'), true) : null;
        
        $paramSet = [
            'wiki' => $this->dataset, 
            'config' => Config::all(),
            'namespace' => Namespaces::all(), 
            'lang' => Languages::all(),
            'skinConfig' => $View->skin->config,
            'skinName' => $View->skin->name,
            'uri_data' => (array) $this->uri_data,
            'request_uri' => $_SERVER['REQUEST_URI'],
            'post' => $_POST,
            'license' => $license
        ];

        if (isset($this->error))
            $paramSet['error'] = (array) $this->error;
        else
            $paramSet['error'] = [];

        if (isset($this->alert))
            $paramSet['alert'] = (array) $this->alert;
        else
            $paramSet['alert'] = [];

        $View->params = $paramSet;
        return $View->renderPage();
    }

    public function makePage(bool $getPage = true)
    {
        $this->dataset = (array) [
            'config' => $this->api_config,
            //'local_config' => $local_config,
            'page' => $this->page,
            'session' => $this->session
        ];

        if ($getPage) {
            echo $this->getPage();
        }
    }

    /**
     * Parse namespace and title in full title.
     * 
     * @param string $title     Full title of document
     * @return array            array(Namespace, Title)
     */
    public static function parseTitle(string $title): array
    {
        $t = explode(':', $title);
        
        if (!in_array($t[0], Namespaces::all()) || count($t) === 1)
            return [Namespaces::DOCUMENT, $title];
        else
            return [$t[0], implode(':', array_slice($t, 1))];
    }

    /**
     * Parse namespace and title in full title.
     * 
     * @param string $namespace     raw namespace of document
     * @param string $title     title of document
     * @return string           formed title
     */
    public static function makeTitle(string $namespace, string $title): string
    {
        if (self::forceShowNamespace($namespace, $title) === false)
            return $title;
        else 
            return $namespace.':'.$title;
    }

    protected static function forceShowNamespace(string $namespace, string $title): bool|null
    {
        $e = explode(':', $title);
        if (!in_array($e[0], Namespaces::all()) && $namespace == Namespaces::DOCUMENT)
            return false;
        else
            return null;
    }

    protected static function sendMail(string $recipient, string $title, string $content): bool
    {
        $mail = Config::get('mail.smtp_password');
        $mailer = new Mailer([
            'host' => Config::get('mail.smtp_host'),
            'username' => Config::get('mail.smtp_username'),
            'password' => Config::get('mail.smtp_password'),
            'port' => Config::get('mail.smtp_port'),
            'encryption' => strtolower(Config::get('mail.smtp_protocol'))
        ]);
        $result = $mailer
            ->setSubject($title)
            ->setBody($content)
            ->setTo([$recipient])
            ->setFrom([$mail['smtp_address'] => Config::get('wiki.site_name_en')])
            ->send();

        return $result;
    }

    /**
     * Get IP of user.
     */
    protected static function getIPAddr(): string
    {
        return $_SERVER['REMOTE_ADDR'];
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
    protected static function rand(int $len=16, bool $u=false, string $add=''): string
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

    protected static function utf8_ord($c)
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
    
    protected static function is_hangeul(string $c)
    {
        $o = self::utf8_ord($c);
        if( 0x1100<=$o && $o<=0x11FF ) return true;
        if( 0x3130<=$o && $o<=0x318F ) return true;
        if( 0xAC00<=$o && $o<=0xD7A3 ) return true;
        return false;
    }

    protected static function ko_head(string $char)
    {
        $heads = ['ㄱ','ㄲ','ㄴ','ㄷ','ㄸ','ㄹ','ㅁ','ㅂ','ㅃ','ㅅ','ㅆ','ㅇ','ㅈ','ㅉ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ'];
        $code = self::utf8_ord($char) - 44032;
        if ($code > -1 && $code < 11172) {
            $result = $heads[$code / 588];
        }else{//if(in_array($char, $heads)) {
            $result = $char;
        }
        return $result;
    }
}