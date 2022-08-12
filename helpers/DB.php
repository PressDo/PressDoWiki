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
            switch(Config::get('db_type')){
                case 'mysql':
                case 'mariadb':
                case 'pgsql':
                case 'cubrid':
                    $dsn = Config::get('db_type').':dbname='.Config::get('db_name').';host='.Config::get('db_host').';port='.Config::get('db_port').';charset:utf8';
                    break;
                case 'oracle':
                    $dsn = 'oci:dbname='.Config::get('db_host').'/'.Config::get('db_name').';charset=utf8';
                    break;
                case 'mssql':
                    $dsn = 'dblib:dbname='.Config::get('db_name').';host='.Config::get('db_host').';port='.Config::get('db_port').';charset:utf8';
                    break;
                case 'firebird':
                    $dsn = 'firebird:dbname='.Config::get('db_host').':'.Config::get('db_name').';charset:utf8';
                    break;
                case 'db2':
                    $dsn = 'ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE='.Config::get('db_name').';HOSTNAME='.Config::get('db_host').';PORT='.Config::get('db_port').';PROTOCOL=TCPIP;UID='.Config::get('db_user').';PWD='.Config::get('db_pass');
                    break;
                case 'sqlite':
                    $dsn = 'sqlite:'.Config::get('db_name');
                    break;
                default:
                    break;
            }
            
            static::$instance = (Config::get('db_type') !== 'db2')? new \PDO($dsn, Config::get('db_user'), Config::get('db_pass')): new \PDO($dsn, '', '');
            
            if(!static::$instance){
                throw new ErrorException('ERROR_DBCONNECT');
            }
        }
        return static::$instance;
    }
}