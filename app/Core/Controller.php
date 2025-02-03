<?php
namespace PressDo\app\Core;

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
        $_ns = Namespaces::all();
        $t = explode(':', $title);
        
        if (!in_array($t[0], $_ns) || count($t) === 1)
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
        if ($namespace == Namespaces::DOCUMENT && Config::get('wiki', 'force_show_namespace') === false)
            return $title;
        else 
            return $namespace.':'.$title;
    }

    protected static function sendMail(string $recipient, string $title, string $content): bool
    {
        $mail = Config::get('mail');
        $mailer = new Mailer([
            'host' => $mail['smtp_host'],
            'username' => $mail['smtp_username'],
            'password' => $mail['smtp_password'],
            'port' => $mail['smtp_port'],
            'encryption' => strtolower($mail['smtp_protocol'])
        ]);
        $result = $mailer
            ->setSubject($title)
            ->setBody($content)
            ->setTo([$recipient])
            ->setFrom([$mail['smtp_address'] => Config::get('wiki', 'site_name_en')])
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
}