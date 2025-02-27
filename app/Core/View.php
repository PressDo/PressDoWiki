<?php
namespace PressDo\app\Core;


use PressDo\app\Core\Controller;
use PressDo\app\Helpers\Config;
use Latte\Engine as Latte;


class View
{
    public Latte $latte;

    public object $skin;

    public array $params;

    public function __contruct()
    {
    }

    /**
     * Initialize Frontend.
     */
    public function renderInit()
    {
        $this->latte = new Latte;

        $this->latte->addFilter('localdate',
            fn(int $t): string => '<time datetime="'.gmdate('Y-m-d\TH:i:s', $t).'.000Z">'.date('Y-m-d H:i:s', $t).'</time>');
        
        $this->latte->addFilter('localreldate',
            fn(int $t): string => '<time datetime="'.gmdate('Y-m-d\TH:i:s', $t).'.000Z">'.Controller::formatBefore($t).'</time>');
        
        $this->latte->setTempDirectory('../temp');
        $this->latte->addFilter('formatTime', fn($time) => Controller::formatTime($time));

        $this->skin = new \stdClass;
        $this->skin->name = $this->session['member']['settings']['skin_name'] ?? Config::get('wiki.default_skin');
        $this->skin->config = json_decode(file_get_contents('skins/'.$this->skin->name.'/config.json'), true);
    }

    public function renderPage(): string
    {
        switch($this->params['uri_data']['page']){
            case 'LongestPages':
            case 'NeededPages':
            case 'OldPages':
            case 'OrphanedPages':
            case 'ShortestPages':
            case 'UncategorizedPages':
            case 'RandomPage':
                $file = '../app/Views/layouts/pagelist.latte';
                break;
            case 'admin':
            case 'member':
                $file = '../app/Views/layouts/'.$this->params['uri_data']['page'].'/'.$this->params['uri_data']['menu'].'.latte';
                break;
            case 'new_edit_request':
            case 'edit_request':
                $file = '../app/Views/layouts/edit.latte';
                if ($this->params['uri_data']['action'] == 'edit')
                    break;
            default:
                $file = '../app/Views/layouts/'.$this->params['uri_data']['page'].'.latte';
        }

        if($this->params['wiki']['page']['view_name'] == 'error')
            $file = '../app/Views/layouts/error.latte';
        elseif($this->params['wiki']['page']['view_name'] == 'notfound')
            $file = '../app/Views/layouts/notfound.latte';

        $this->params['innerLayout'] = $this->latte->renderToString($file, $this->params);
        $this->params['body'] = $this->latte->renderToString('skins/'.$this->skin->name.'/layout.latte', $this->params);
        
        return $this->latte->renderToString('../app/Views/frame.latte', $this->params);
    }
}