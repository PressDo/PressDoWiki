<?php
namespace PressDo\App\Controllers\Pages\Member;

use PressDo\App\Models\Member;
use PressDo\App\Core\Controller;
use PressDo\App\Helpers\{Languages,Config};
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class ActivateOtp extends Controller
{
    public function makeData()
    {
        if(!$this->session['member']){
            Header('Location: /member/login?redirect='.$_SERVER['REQUEST_URI']);
            exit;
        }

        $user = Member::getUserInfo($this->session['uuid']);

        if($user['totp_secret'] !== null){
            $error = ['code' => 'already_activated_otp'];
            $page = [
                'view_name' => 'error',
                'title' => Languages::get('page')['error'],
                'data' => $error
            ];
            return $page;
        }

        if (!empty($_POST['pin'])) {
            $otp = TOTP::createFromSecret($this->session['otp_secret']);

            if ($otp->verify($_POST['pin'])) {
                Member::setTotp($this->session['uuid'], $this->session['otp_secret']);
                unset($this->session['member']['secret']);
                header('Location: /member/mypage');
            } else
                $errmsg = 'err_invalid_pin';
            
            if (empty($this->session['otp_secret']))
                $this->session['otp_secret'] = trim(Base32::encodeUpper(random_bytes(10)), '=');
        } else {
            $this->session['otp_secret'] = trim(Base32::encodeUpper(random_bytes(10)), '=');
        }

        $writer = new PngWriter();
        $qrCode = new QrCode(
            data: 'otpauth://totp/'
                .urlencode(Config::get('wiki.site_name')).':'
                .$this->session['member']['username']
                .'?secret='.$this->session['otp_secret'].'&period=30&digits=6&algorithm=SHA1&issuer='.urlencode(Config::get('wiki.site_name')),
            size: 244,
            margin: 0
        );
        $qr_result = $writer->write($qrCode);

        $page = [
            'view_name' => 'activate_otp',
            'title' => Languages::get('page')['activate_otp'],
            'data' => [
                'error' => $errmsg,
                'qrcode' => $qr_result->getDataUri(),
                'redirect' => base64_decode($_GET['redirect'])
            ],
            'menus' => [],
            'customData' => []
        ];

        return $page;
    }
}