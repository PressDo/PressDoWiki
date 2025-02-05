<?php
namespace PressDo\app\Helpers;

use ErrorException;
use \PDO as PDO;

class Database
{
    private static $instance = null;

    /**
     * get database instance
     */
    public static function getInstance(): PDO
    {
        if(!static::$instance) {
            static::$instance = self::init();
        }
        return static::$instance;
    }

    /**
     * Initialize database.
     */
    private static function init()
    {
        if(!static::$instance) {
            switch(DefaultConfig::get('database.type')){
                case 'mysql':
                    // no break
                case 'pgsql':
                    // no break
                case 'cubrid':
                    $dsn = DefaultConfig::get('database.type').':dbname='.DefaultConfig::get('database.name').';host='.DefaultConfig::get('database.host').';port='.DefaultConfig::get('database.port').';charset:utf8';
                    break;
                case 'oracle':
                    $dsn = 'oci:dbname='.DefaultConfig::get('database.host').'/'.DefaultConfig::get('database.name').';charset=utf8';
                    break;
                case 'mssql':
                    $dsn = 'dblib:dbname='.DefaultConfig::get('database.name').';host='.DefaultConfig::get('database.host').';port='.DefaultConfig::get('database.port').';charset:utf8';
                    break;
                case 'firebird':
                    $dsn = 'firebird:dbname='.DefaultConfig::get('database.host').':'.DefaultConfig::get('database.name').';charset:utf8';
                    break;
                case 'db2':
                    $dsn = 'ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE='.DefaultConfig::get('database.name').';HOSTNAME='.DefaultConfig::get('database.host').';PORT='.DefaultConfig::get('database.port').';PROTOCOL=TCPIP;UID='.DefaultConfig::get('database.user').';PWD='.DefaultConfig::get('database.password');
                    break;
                case 'sqlite':
                    $dsn = 'sqlite:'.DefaultConfig::get('database.name');
                    break;
                default:
                    break;
            }
            
            static::$instance = DefaultConfig::get('database.type') !== 'db2' ? new PDO($dsn, DefaultConfig::get('database.user'), DefaultConfig::get('database.password')) : new PDO($dsn, '', '');
            
            if (!static::$instance) {
                throw new ErrorException('Cannot connect to database.');
            }
        }
        return static::$instance;
    }
}