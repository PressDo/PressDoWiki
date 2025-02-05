<?php
namespace PressDo\app\Helpers;

class Router
{
    public object $uri_data;

    /**
     * Parse requested URI
     * @param string $request_uri
     * @return void
     */
    public function handleURI(string $request_uri): void
    {   
        if ($request_uri == '/'):
            Header('Location: /w/'.rawurlencode(Config::get('wiki.front_page')));
            exit;
        endif;

        // (0)/1/2/3
        $uriset = explode('/', explode('?', $request_uri)[0]);
        $uri_data = (object) [
            'page' => $uriset[1] == 'w' ? 'wiki' : $uriset[1]
        ];

        if (count($uriset) > 2 && !in_array($uri_data->page, ['member', 'admin', 'api'])) {
            $uri_data->title = urldecode(implode('/', array_slice($uriset, 2)));
            $uri_data->titleurl = implode('/', array_slice($uriset, 2));
        } else {
            $uri_data->menu = $uriset[2];
            $uri_data->title = urldecode(implode('/', array_slice($uriset, 3)));
            $uri_data->titleurl = implode('/', array_slice($uriset, 3));
        }

        $this->uri_data = $uri_data;
    }
}