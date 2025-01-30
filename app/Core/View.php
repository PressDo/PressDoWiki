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
        $this->latte->setTempDirectory('../temp');
        $this->latte->addFilter('formatTime', fn($time) => Controller::formatTime($time));

        $this->skin = new \stdClass;
        $this->skin->name = $this->session['member']['settings']['skin_name'] ?? Config::get('default_skin');
        $this->skin->config = json_decode(file_get_contents('skins/'.$this->skin->name.'/config.json'), true);
    }

    public function renderPage(): string
    {
        switch($this->params['uri_data']['page']){
            case 'admin':
            case 'member':
                $file = '../app/Views/layouts/'.$this->params['uri_data']['page'].'/'.$this->params['uri_data']['menu'].'.latte';
                break;
            default:
                $file = '../app/Views/layouts/'.$this->params['uri_data']['page'].'.latte';
        }

        if($this->params['wiki']['page']['view_name'] == 'error')
            $file = '../app/Views/layouts/error.latte';

        $this->params['innerLayout'] = $this->latte->renderToString($file, $this->params);
        $this->params['body'] = $this->latte->renderToString('skins/'.$this->skin->name.'/layout.latte', $this->params);
        
        return $this->latte->renderToString('../app/Views/frame.latte', $this->params);
    }
}