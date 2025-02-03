<?php
namespace PressDo;

require '../vendor/autoload.php';
require '../app/Core/Helper.php';
require '../app/Core/Controller.php';
require '../app/Core/Model.php';
require '../app/Core/View.php';
require '../app/Controllers/ACL.php';
require 'classLoader.php';

use PressDo\app\Helpers\{Config,Router};

date_default_timezone_set(Config::get('timezone'));
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

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
        $fnClassNm = $router->uri_data->page.'\\'.ucwords($router->uri_data->menu, '_');
        require '../app/Controllers/Pages/'.$router->uri_data->page.'/'.ucwords($router->uri_data->menu, '_').'.php';
        break;
    case 'acl':
        $fnClassNm = 'ACL';
        require '../app/Controllers/Pages/ACL.php';
        break;
    default:
        $fnClassNm = ucwords($router->uri_data->page, '_');
        require '../app/Controllers/Pages/'.ucwords($router->uri_data->page, '_').'.php';
}

// initial
$wiki = (new \ReflectionClass('PressDo\app\Controllers\Pages\\'.$fnClassNm))->newInstance();
$wiki->uri_data = $router->uri_data;

if(!empty($_SESSION))
    $wiki->session = $_SESSION;

// Controller
$wiki->page = $wiki->makeData();

// call page
$getPage = $router->uri_data->page !== 'api';
$wiki->makePage($getPage);

$_SESSION = $wiki->session;