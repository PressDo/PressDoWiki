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
    public static function RecentChanges(string $option='', bool $sidebar=false)
    {
        $db = self::db();

        if($sidebar)
            $quota = '15';
        else
            $quota = '100';

        try {
            $d = $db->query("SELECT h.`uuid`,h.`action`,h.`comment`,h.`reverted_version`,h.`count`,h.`contributor_m`, h.`contributor_i`,h.`document`, h.`acl_changed`, h.`moved_from`, h.`moved_to`, h.`datetime`, h.rev FROM `history` as h, document 
            WHERE BINARY h.`is_hidden`='false' AND h.document = document.uuid AND document.namespace != '사용자' $option ORDER BY `datetime` DESC LIMIT ".$quota);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 최근 변경 가져오는 중 오류 발생');
        }
        return $d->fetchAll(\PDO::FETCH_ASSOC);
    }
}