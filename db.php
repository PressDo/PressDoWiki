<?php
define('PressDo_Config', true);
require __DIR__.'/data/global/config.php';
session_start();
switch($conf['DBType']){
case 'mysql':
case 'pgsql':
case 'cubrid':
    $dsn = $conf['DBType'].':dbname='.$conf['DBName'].';host='.$conf['DBHost'].';port='.$conf['DBPort'].';charset:utf8';
    break;
case 'oracle':
    $dsn = 'oci:dbname='.$conf['DBHost'].'/'.$conf['DBName'].';charset=utf8';
    break;
case 'mssql':
    $dsn = 'dblib:dbname='.$conf['DBName'].';host='.$conf['DBHost'].';port='.$conf['DBPort'].';charset:utf8';
    break;
case 'firebird':
    $dsn = 'firebird:dbname='.$conf['DBHost'].':'.$conf['DBName'].';charset:utf8';
    break;
case 'db2':
    $dsn = 'ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE='.$conf['DBName'].';HOSTNAME='.$conf['DBHost'].';PORT='.$conf['DBPort'].';PROTOCOL=TCPIP;UID='.$conf['DBUser'].';PWD='.$conf['DBPass'];
    break;
case 'sqlite':
    $dsn = 'sqlite:'.$conf['DBHost'];
    break;
default:
    break;
}
    ($conf['DBType'] !== 'db2')? $db = new PDO($dsn, $conf['DBUser'], $conf['DBPass']): $db = new PDO($dsn, '', '');
    if(!$db) return false;
$_DB_pINT = PDO::PARAM_INT;
