<?php
namespace PressDo\App\Controllers\Pages\admin;

use PressDo\App\Models\{Member,ACL};
use PressDo\App\Core\Controller;
use PressDo\App\Helpers\{Languages,Config as Conf};

class Config extends Controller
{
    public function makeData(): array
    {
        $perms = [];

        if(!empty($this->session['member']))
            ACL::getAccountPerms($this->session['uuid'], $this->session['member']['username'], $perms);

        if(!in_array('config', $perms) && !in_array('developer', $perms)) {
            $error = ['code' => 'no_permission'];
            $page = [
                'view_name' => 'error',
                'title' => Languages::get('page', 'error'),
                'data' => $error
            ];
            return $page;
        }
        
        if (!empty($_POST['key']) && !empty($_POST['value'])) {
            // add config
            Conf::set($_POST['key'], $_POST['value']);
        } elseif (!empty($_POST['delk']) && !empty($_POST['delv'])) {
            // delete config
            Conf::delete($_POST['delk'], $_POST['delv']);
        } elseif (!empty($_POST['ckey-1'])) {
            // modify config
            $idx = 1;
            $inputArray = [];
            while (!empty($_POST['ckey-'.$idx]) && $_POST['cval-'.$idx] !== null) {
                array_push($inputArray, $_POST['ckey-'.$idx], $_POST['cval-'.$idx]);
                $idx++;
            }

            Conf::setBulk($inputArray);
        }

        
        $cset = Conf::getPreference();

        $page = [
            'view_name' => 'config',
            'title' => 'Config',//Languages::get('page', 'config'),
            'data' => [
                'config' => $cset
            ],
            'menus' => [],
            'customData' => []
        ];

        return $page;
    }
}