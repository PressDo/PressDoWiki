<?php
namespace PressDo\App\Helpers;

class Router
{
    public object $uri_data;

    /**
     * Array with uri segments.
     * indexed like [0]/[1]/[2]...
     * @var array
     */
    private array $uriset;

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
        $this->uriset = explode('/', $uripath);

        $uri_data = (object) [
            'page' => $this->getUriSegment(1) == 'w' ? 'wiki' : $this->getUriSegment(1),
            'path' => $uripath
        ];

        if (count($this->uriset) > 2 && !in_array($uri_data->page, ['member', 'admin', 'api'])) {
            $uri_data->title = urldecode($this->getUriSegmentFrom(2));
            if ($this->getUriSegment(1) == 'contribution') {
                // 0/menu/target/type
                [
                    ,$uri_data->menu,
                    $uri_data->target,
                    $uri_data->type
                ] = $this->uriset;
            } elseif ($this->getUriSegment(1) == 'edit_request') {
                $uri_data->title = $this->getUriSegment(2);
                $uri_data->action = $this->getUriSegment(3);
            }
        } else {
            if ($this->getUriSegment(2) == 'star' || $this->getUriSegment(2) == 'unstar') {
                $uri_data->menu = $this->getUriSegment(2);
                $uri_data->title = $this->getUriSegmentFrom(3);
            } else {
                $uri_data->menu = $this->getUriSegmentFrom(2);
                $uri_data->title = '';
            }
        }

        $this->uri_data = $uri_data;
    }

    public function getUriSegment(int $idx): string
    {
        return $this->uriset[$idx] ?? '';
    }

    public function getUriSegmentFrom(int $idx): string
    {
        return implode('/', array_slice($this->uriset, $idx)) ?? '';
    }
}