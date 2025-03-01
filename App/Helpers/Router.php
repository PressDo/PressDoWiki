<?php
namespace PressDo\App\Helpers;

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
        elseif ($request_uri == '/opensearch.xml'):
            Header('Content-Type: application/xml');
            $xml = new \SimpleXMLElement('<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" xmlns:moz="http://www.mozilla.org/2006/browser/search/"></OpenSearchDescription>');
            $xml->addChild('ShortName', Config::get('wiki.site_name'));
            $xml->addChild('Description', Config::get('wiki.site_name'));
            $xml->addChild('InputEncoding', 'UTF-8');
            $xmlimg = $xml->addChild('Image', Config::get('wiki.canonical_url').'/favicon.ico');
            $xmlimg->addAttribute('width', '16');
            $xmlimg->addAttribute('height', '16');
            $xmlurl = $xml->addChild('Url');
            $xmlurl->addAttribute('type', 'text/html');
            $xmlurl->addAttribute('method', 'GET');
            $xmlurl->addAttribute('template', Config::get('wiki.canonical_url').'/Go?q={searchTerms}');
            $xml->addChild('xmlns:moz:SearchForm', Config::get('wiki.canonical_url'));
            echo $xml->asXML();
            exit;
        endif;

        $uripath = parse_url($request_uri, PHP_URL_PATH);

        // (0)/1/2/3
        $uriset = explode('/', $uripath);
        $uri_data = (object) [
            'page' => $uriset[1] == 'w' ? 'wiki' : $uriset[1],
            'path' => $uripath
        ];

        if (count($uriset) > 2 && !in_array($uri_data->page, ['member', 'admin', 'api'])) {
            $uri_data->title = urldecode(implode('/', array_slice($uriset, 2)));
            if ($uriset[1] == 'contribution') {
                // 0/menu/target/type
                [
                    ,$uri_data->menu,
                    $uri_data->target,
                    $uri_data->type
                ] = $uriset;
            } elseif ($uriset[1] == 'edit_request') {
                $uri_data->title = $uriset[2];
                $uri_data->action = $uriset[3];
            }
        } else {
            if ($uriset[2] == 'star' || $uriset[2] == 'unstar') {
                $uri_data->menu = $uriset[2];
                $uri_data->title = implode('/', array_slice($uriset, 3));
            } else {
                $uri_data->menu = implode('/', array_slice($uriset, 2));
                $uri_data->title = '';
            }
        }

        $this->uri_data = $uri_data;
    }
}