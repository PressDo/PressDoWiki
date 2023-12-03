<?php
namespace PressDo;
require 'helpers/Config.php';
use PressDo\Config;
class Router {
    // Parse requested URI
    public static function handle_uri($request_uri, $query_string) : object|null
    {   
        if($request_uri == '/'):
            Header('Location: /w/'.rawurlencode(Config::get('frontpage')));
            exit;
        endif;

        // 0/1/2/3
        $uriset = explode('/', explode('?', $request_uri)[0]);
        $uri_data = (object) [
            'page' => $uriset[1]=='w'?'wiki':$uriset[1]
        ];

        if(count($uriset) > 2 && $uri_data->page !== 'member'){
            $uri_data->title = urldecode(implode('/', array_slice($uriset, 2)));
            $uri_data->titleurl = implode('/', array_slice($uriset, 2));
        }else{
            $uri_data->menu = $uriset[2];
            $uri_data->title = urldecode(implode('/', array_slice($uriset, 3)));
            $uri_data->titleurl = implode('/', array_slice($uriset, 3));
        }

        $query = [];
        parse_str($query_string, $query);
        $uri_data->query = (object) $query;
        
        switch($uri_data->page){
            case 'member':
            case 'admin':
            case 'api':
                require 'controllers/'.$uri_data->page.'/'.$uri_data->menu.'.php';
                break;
            default:
                require 'controllers/'.$uri_data->page.'.php';
        }

        return $uri_data;
    }
}