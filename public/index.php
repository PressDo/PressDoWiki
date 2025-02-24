<?php
namespace PressDo;

require '../vendor/autoload.php';
require '../app/Core/Helper.php';
require '../app/Core/Controller.php';
require '../app/Core/Model.php';
require '../app/Core/View.php';
require '../app/Controllers/ACL.php';
require '../app/Models/Member.php';
require 'classLoader.php';

use PressDo\app\Helpers\{Config,Router,GeoIP};
use PressDo\app\Core\Controller;

date_default_timezone_set(GeoIP::getTimezone(Controller::getIpAddr()) ?? Config::get('wiki.timezone'));
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');

if(!session_id())
    session_start();

$router = new Router();
$router->handleURI($_SERVER['REQUEST_URI']);

loadClass($router->uri_data->page, $router->uri_data->menu);

switch ($router->uri_data->page) {
    case 'member':
        // no break
    case 'admin':
        // no break
    case 'api':
        $fnClassNm = $router->uri_data->page.'\\'.str_replace('_', '', ucwords($router->uri_data->menu, '_'));
        require '../app/Controllers/Pages/'.$router->uri_data->page.'/'.str_replace('_', '', ucwords($router->uri_data->menu, '_')).'.php';
        break;
    case 'acl':
        $fnClassNm = 'ACL';
        require '../app/Controllers/Pages/ACL.php';
        break;
    default:
        $fnClassNm = str_replace('_', '',ucwords($router->uri_data->page, '_'));
        require '../app/Controllers/Pages/'.str_replace('_', '',ucwords($router->uri_data->page, '_')).'.php';
}

// initial
$wiki = (new \ReflectionClass('PressDo\app\Controllers\Pages\\'.$fnClassNm))->newInstance();
$wiki->uri_data = $router->uri_data;

header("Content-Security-Policy: default-src 'self'; img-src 'self' *.theseed.io secure.gravatar.com www.google-analytics.com 
http://tn-skr2.smilevideo.jp data:; media-src *; child-src *; script-src 'self' 'unsafe-eval' 'unsafe-inline' www.google.com www.gstatic.com 
www.googletagmanager.com www.google-analytics.com; style-src 'self' 'unsafe-inline' fonts.googleapis.com; connect-src 'self'; font-src 'self' 
fonts.gstatic.com data:;");

if(!empty($_SESSION))
    $wiki->session = $_SESSION;

// Controller
$wiki->page = $wiki->makeData();

// call page
$getPage = $router->uri_data->page !== 'api';
$wiki->makePage($getPage);

$_SESSION = $wiki->session;