<?php
namespace PressDo\App\Core;

use PressDo\App\Models\Member;
use PressDo\App\Helpers\{Config,Languages,Namespaces,DefaultConfig};
use PressDo\App\Services\Mark\MarkHandler;
use yidas\socketMailer\Mailer;
use SVG\SVG;

class Controller
{
    public object $uri_data;

    public array $error;
    public array $session, $api_config, $array, $dataset;
    public $page, $alert;

    public function __construct()
    {
        if (!empty($_SESSION))
            $this->session = $_SESSION;
        else {
            $this->session = [
                'menus' => [],
                'member' => null,
                'ip' => self::getIPAddr(),
                'ua' => $_SERVER['HTTP_USER_AGENT']
            ];
        }
        
        if (empty($this->session['member']) && !empty($_COOKIE['szczecin'])) {
            // auto-login
            $uuid = Member::checkCookie('szczecin', $_COOKIE['szczecin']);
            
            if ($uuid !== null) {
                // valid cookie
                $l = Member::login($uuid, $this->session['ip'], $_SERVER['HTTP_USER_AGENT']);
                $sess = self::getMemberData($uuid, $l['email'], $l['username'], $l['skin']);
                $this->session['menus'] = $sess['menus'];
                $this->session['member'] = $sess['member'];
                $this->session['uuid'] = $sess['uuid'];
                $this->session['admin'] = $sess['admin'];
            }
        }
        $ipuuid = Member::getIpUuid($this->session['ip'], true);
        if ($ipuuid !== null)
            $this->session['uuid'] = $ipuuid;

        if (Config::get('wiki.use_captcha')) {
            $captchaClassName = 'PressDo\App\Helpers\Captcha\\'.DefaultConfig::get('captcha.type');
            $this->api_config = [
                'captcha_api_endpoint' => $captchaClassName::API_ENDPOINT,
                'captcha_class_name' => $captchaClassName::CLASS_NAME,
                'captcha_token_name' => $captchaClassName::TOKEN_NAME
            ];
        }
    }

    public static function getMemberData(string $uuid, string $email, string $username, string $skin): array
    {
        $menus = [];
        $SP = ['aclgroup', 'grant', 'login_history'];
        $link = [
            'aclgroup' => '/aclgroup',
            'grant' => '/admin/grant',
            'login_history' => '/admin/login_history',
            'batch_revert' => 'batch_revert'
        ];
        $sps = Member::specialPerms($uuid);
        foreach ($SP as $prm) {
            if (in_array($prm, $sps))
                array_push($menus, ['l' => $link[$prm], 't' => $prm]);
        }

        return [
            'menus' => $menus,
            //'email' => $email,
            'admin' => in_array('admin', $sps),
            'uuid' => $uuid,
            'member' => [
                'user_document_discuss' => null,
                'username' => $username,
                'gravatar_url' => '//www.gravatar.com/avatar/'.md5($email).'?d=retro',
                'admin' => in_array('admin', $sps),
                'settings' => ['skin' => $skin]
            ]
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
        return MarkHandler::load($content, $options);
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
            'license' => $license,
            'api_config' => $this->api_config
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
            return [Namespaces::document(), $title];
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
        if (!in_array($e[0], Namespaces::all()) && $namespace == Namespaces::document())
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

    protected static function editFormProcess(Controller &$page, string $tokennm): bool
    {
        // Edit Submission
        $_POST['content'] = htmlspecialchars_decode($_POST['content']);

        $content = preg_replace('/^#(redirect|넘겨주기) (.+)$/im', '#redirect $2', $_POST['content']);

        // ignore string after redirect
        if (preg_match('/^#redirect (.+)$/im', $content, $matches)) {
            $content = $matches[0];
        }

        if ($_POST['token'] !== $page->session[$tokennm]) {
            // Reject: wrong anti-CSRF token
            $page->error = self::makeErrorBox('err_csrf_token');
        } elseif ($page->session['raw'] == $content) {
            // Reject: same doc content
            $page->error = self::makeErrorBox('err_same_contents');
        } else {
            // Approve Edit
            $page->content = $content;
            return true;
        }
        return false;
    }

    protected static function validateCaptcha(?string $token): bool
    {
        if (Config::get('wiki.use_captcha'))
            return true;

        if (empty($token))
            return false;

        $captchaClassName = 'PressDo\App\Helpers\Captcha\\'.DefaultConfig::get('captcha.type');
        return $captchaClassName::verify($token);
    }

    protected static function makeErrorBox(string $code)
    {
        return [
            'code' => $code,
            'message' => Languages::get('msg', $code) ?? $code,
            'errbox' => true
        ];
    }

    /**
     * Load diff between two strings in HTML
     * @param string $old
     * @param string $new
     * @param string $caption
     * @return string
     */
    protected static function load_diff(string $old, string $new, string $caption=''): string
    {
        require '../App/Helpers/Libraries/diff/Diff.php';
        require '../App/Helpers/Libraries/diff/Inline.php';

        $a = explode("\n", $old);
        $b = explode("\n", $new);

        $options = array(
            //'ignoreWhitespace' => true,
            //'ignoreCase' => true,
        );

        $diff = new \Diff($a, $b, $options); 
        $ren = new \Diff_Renderer_Html_Inline;
        $ren->caption = $caption;
        $ren->linecnt = [count($a),count($b)];

        return $diff->render($ren);
    }

    /**
     * Get IP of user.
     */
    public static function getIPAddr(): string
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

    public static function formatBefore(int $timestamp): string
    {
        $diff = time() - $timestamp;
        if ($diff < 10):
            return Languages::get('recent', 'rightbefore');
        elseif ($diff < 60):
            $time = $diff;
            $unit = 'second';
        elseif ($diff < 3600):
            $time = $diff / 60;
            $unit = 'minute';
        elseif ($diff < 86400):
            $time = $diff / 3600;
            $unit = 'hour';
        elseif ($diff < 2592000):
            $time = $diff / 86400;
            $unit = 'day';
        else:
            return date('Y-m-d H:i:s', $timestamp);
        endif;

        return sprintf(Languages::get('recent', 'before'), floor($time), Languages::get('acl', $unit));
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
        if ($u)
            $c .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if (strlen($add) > 0)
            $c .= $add;
        
        $cl = strlen($c);
        $s = '';
        for ($i=0; $i<$len; $i++) 
            $s .= $c[rand(0, $cl-1)];
        
        return $s;
    }

    protected static function utf8_ord($c)
    {
        $len = strlen($c);
        if ($len <= 0)
            return false;
        $h = ord($c[0]);
        if ($h <= 0x7F)
            return $h;
        if ($h < 0xC2)
            return false;
        if ($h <= 0xDF && $len>1)
            return ($h & 0x1F) <<  6 | (ord($c[1]) & 0x3F);
        if ($h <= 0xEF && $len>2)
            return ($h & 0x0F) << 12 | (ord($c[1]) & 0x3F) <<  6 | (ord($c[2]) & 0x3F);		  
        if ($h <= 0xF4 && $len>3)
            return ($h & 0x0F) << 18 | (ord($c[1]) & 0x3F) << 12 | (ord($c[2]) & 0x3F) << 6 | (ord($c[3]) & 0x3F);
        return false;
    }

    protected static function utf8_chr($num) {
        if ($num<128)
            return chr($num);
        if ($num<2048)
            return chr(($num>>6)+192).chr(($num&63)+128);
        if ($num<65536)
            return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
        if ($num<2097152)
            return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128).chr(($num&63)+128);
        return false;
     }
    
    protected static function is_hangeul(string $c)
    {
        $o = self::utf8_ord($c);
        if (0x1100<=$o && $o<=0x11FF )
            return true;
        if (0x3130<=$o && $o<=0x318F )
            return true;
        if (0xAC00<=$o && $o<=0xD7A3 )
            return true;
        return false;
    }

    protected static function ko_head(string $char)
    {
        $chars = ['ㄱ','ㄲ','ㄴ','ㄷ','ㄸ','ㄹ','ㅁ','ㅂ','ㅃ','ㅅ','ㅆ','ㅇ','ㅈ','ㅉ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ'];
        $code = self::utf8_ord($char) - 44032;
        if ($code > -1 && $code < 11172) {
            $result = $chars[$code / 588];
        } else {//if(in_array($char, $chars)) {
            // not hangul
            $result = $char;
        }
        return $result;
    }

    protected static function ko_last(string $char)
    {
        $chars = ['','ㄱ','ㄲ','ㄳ','ㄴ','ㄵ','ㄶ','ㄷ','ㄹ','ㄺ','ㄻ','ㄼ','ㄽ','ㄾ','ㄿ','ㅀ','ㅁ','ㅂ','ㅄ','ㅅ','ㅆ','ㅇ','ㅈ','ㅊ','ㅋ',' ㅌ','ㅍ','ㅎ'];
        $code = self::utf8_ord($char) - 44032;
        if ($code > -1 && $code < 11172) {
            $result = $chars[$code % 28];
        } else {//if(in_array($char, $chars)) {
            // not hangul
            $result = '';
        }
        return $result;
    }

    protected static function ko_exceptlast(string $char)
    {
        $code = self::utf8_ord($char) - 44032;
        return self::utf8_chr($code - ($code % 28) + 44032);
    }

    public static function getTransparentBackground(int $width, int $height): string
    {
        $image = new SVG($width, $height);
        $doc = $image->getDocument();
        return $image;
    }

    public static function getCookieOptions(int $duration): array
    {
        return [
            'expires' => $_SERVER['REQUEST_TIME'] + $duration,
            'path' => '/',
            'domain' => Config::get('wiki.domain'),
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ];
    }
}