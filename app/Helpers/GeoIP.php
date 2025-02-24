<?php
namespace Pressdo\App\Helpers;

use GeoIp2\Database\Reader;

class GeoIP
{
    public const GEOCODES = ['AD','AE','AF','AG','AI','AL','AM','AO','AQ','AR','AS','AT','AU','AW','AX','AZ','BA','BB','BD','BE','BF','BG','BH','BI','BJ','BL','BM','BN','BO','BQ','BR','BS','BT','BV','BW','BY','BZ','CA','CC','CD','CF','CG','CH','CI','CK','CL','CM','CN','CO','CR','CU','CV','CW','CX','CY','CZ','DE','DJ','DK','DM','DO','DZ','EC','EE','EG','EH','ER','ES','ET','FI','FJ','FK','FM','FO','FR','GA','GB','GD','GE','GF','GG','GH','GI','GL','GM','GN','GP','GQ','GR','GS','GT','GU','GW','GY','HK','HM','HN','HR','HT','HU','ID','IE','IL','IM','IN','IO','IQ','IR','IS','IT','JE','JM','JO','JP','KE','KG','KH','KI','KM','KN','KP','KR','KW','KY','KZ','LA','LB','LC','LI','LK','LR','LS','LT','LU','LV','LY','MA','MC','MD','ME','MF','MG','MH','MK','ML','MM','MN','MO','MP','MQ','MR','MS','MT','MU','MV','MW','MX','MY','MZ','NA','NC','NE','NF','NG','NI','NL','NO','NP','NR','NU','NZ','OM','PA','PE','PF','PG','PH','PK','PL','PM','PN','PR','PS','PT','PW','PY','QA','RE','RO','RS','RU','RW','SA','SB','SC','SD','SE','SG','SH','SI','SJ','SK','SL','SM','SN','SO','SR','SS','ST','SV','SX','SY','SZ','TC','TD','TF','TG','TH','TJ','TK','TL','TM','TN','TO','TR','TT','TV','TW','TZ','UA','UG','UM','US','UY','UZ','VA','VC','VE','VG','VI','VN','VU','WF','WS','YE','YT','ZA','ZM','ZW'];

    public static Reader $geoip;

    /**
     * Initialize configs.
     */
    private static function init(): void
    {
        if(empty(static::$geoip)) {
            static::$geoip = new Reader(Config::get('wiki.geoip2_database'));
        }
    }

    private static function getGeoIP2(string $ip): string
    {
        self::init();
        return static::$geoip->city($ip)->country->isoCode;
    }

    public static function getTimezone(string $ip): string
    {
        self::init();
        return static::$geoip->city($ip)->location->timeZone;
    }

    /**
     * Get Geolocation Code by IP
     * @param string $ip
     * @return string ISO 3166-1 alpha Country Code
     */
    public static function get(string $ip): string
    {
        if(inet_pton($ip) === false)
            throw new \ErrorException('Must provide a valid IP address to a GeoIP function.');

        if (function_exists('geoip_country_code_by_name')) {
            // php-geoip function
            return geoip_country_code_by_name($ip);
        } elseif (file_exists(Config::get('wiki.geoip2_database'))) {
            // maxmind geoip2 database
            return self::getGeoIP2($ip);
        } else {
            return 'UNAVAILABLE';
        }
    }
}