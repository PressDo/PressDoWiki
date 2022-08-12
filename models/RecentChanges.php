<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    /**
     * fetch recent 100 edit histories
     * 
     * @param string|null $option  extra string for filtering
     * @return 
     */
    public static function RecentChanges(string|null $option)
    {
        $db = self::db();
        try {
            $d = $db->query("SELECT `docid`,`action`,`comment`,`reverted_version`,`count`,`contributor_m`, `contributor_i`, `acl_changed`, `moved_from`, `moved_to`, `datetime` FROM `document` WHERE BINARY `is_hidden`='false' $option ORDER BY `datetime` DESC LIMIT 100");
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 최근 변경 가져오는 중 오류 발생');
        }
        return $d->fetchAll(\PDO::FETCH_ASSOC);
    }
}