<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    public static function get_upload_info(): array
        {
            $category = $license = [];
            $l = $db->query("SELECT `namespace`,display_name,`name` FROM uploader_license_category WHERE `type`='license'")->fetchAll(\PDO::FETCH_ASSOC);
            foreach($l as $li):
                $license[$li['display_name']] = [
                    'namespace' => Namespaces::get($li['namespace']),
                    'name' => $li['name']
                ];
            endforeach;
            $c = $db->query("SELECT `namespace`,display_name,`name` FROM uploader_license_category WHERE `type`='category'")->fetchAll(\PDO::FETCH_ASSOC);
            foreach($c as $ct):
                $category[$ct['display_name']] = [
                    'namespace' => Namespaces::get($ct['namespace']),
                    'name' => $ct['name']
                ];
            endforeach;
            return ['License' => $license, 'Category' => $category];
        }
}