<?php
namespace PressDo;
use ErrorException;
class DB
{
    protected static $instance = null;

    /**
     * get database instance
     */
    public static function getInstance() : \PDO
    {
        if(!static::$instance) {
            static::$instance = static::init();
        }
        return static::$instance;
    }

    /**
     * Initialize database.
     */
    protected static function init()
    {
        if(!static::$instance) {
            $db = Config::get('database');
            switch($db['type']){
                case 'mysql':
                case 'pgsql':
                case 'cubrid':
                    $dsn = $db['type'].':dbname='.$db['name'].';host='.$db['host'].';port='.$db['port'].';charset:utf8';
                    break;
                case 'oracle':
                    $dsn = 'oci:dbname='.$db['host'].'/'.$db['name'].';charset=utf8';
                    break;
                case 'mssql':
                    $dsn = 'dblib:dbname='.$db['name'].';host='.$db['host'].';port='.$db['port'].';charset:utf8';
                    break;
                case 'firebird':
                    $dsn = 'firebird:dbname='.$db['host'].':'.$db['name'].';charset:utf8';
                    break;
                case 'db2':
                    $dsn = 'ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE='.$db['name'].';HOSTNAME='.$db['host'].';PORT='.$db['port'].';PROTOCOL=TCPIP;UID='.$db['user'].';PWD='.$db['password'];
                    break;
                case 'sqlite':
                    $dsn = 'sqlite:'.$db['name'];
                    break;
                default:
                    break;
            }
            
            static::$instance = ($db['type'] !== 'db2')? new \PDO($dsn, $db['user'], $db['password']): new \PDO($dsn, '', '');
            
            if(!static::$instance){
                throw new ErrorException('ERROR_DBCONNECT');
            }
        }
        return static::$instance;
    }
}