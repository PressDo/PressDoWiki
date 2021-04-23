<?php
# PressDo SQL Core File
# **functions**
# SQL_Query()
# SQL_Assoc()
#
session_start();
define('PressDo_Config', true);
include 'data/global/config.php';
switch($conf['DBType']){
case 'mysql': // php-mysql
    $SQL = new mysqli($conf['DBHost'],$conf['DBUser'],$conf['DBPass'],$conf['DBName'],$conf['DBPort']);
    $SQL->set_charset("utf8");
    if(!$SQL){
        return false;
    }
    function SQL_Query($Command)
    {
        global $SQL;
        $q = $SQL->query($Command);
        return $q;
    }
    function SQL_Assoc($Query)
    {
        return mysqli_fetch_assoc($Query);
    }
break;
case 'pgsql': // php-pgsql
    $DBHost = $conf['DBHost'];
    $DBUser = $conf['DBUser'];
    $DBPass = $conf['DBPass'];
    $DBName = $conf['DBName'];
    $SQL = pg_connect("$DBHost $DBPort $DBName $DBPass");
    if(!$SQL){
        return false;
    }
    function SQL_Query($Command)
    {
        global $SQL;
        $q = pg_exec($SQL, $Command);
        return $q;
    }
    function SQL_Assoc($Query)
    {
        return pg_fetch_assoc($Query);
    }
break;
case 'sqlite': //php-sqlite3
    $SQL = new SQLite3($conf['DBName']);
    if(!$SQL){
        return false;
    }
    function SQL_Query($Command)
    {
        global $SQL;
        return $SQL->query($Command);
    }
    function SQL_Assoc($Query)
    {
        return $Query->fetchArray(SQLITE3_ASSOC);
    }
break;
case 'oracle': // oci8
$SQL = oci_connect($conf['DBUser'], $conf['DBPass'], $conf['DBName']);
    if(!$SQL){
        return false;
    }
    function SQL_Query($Command)
    {
        global $SQL;
        return oci_execute(oci_parse($SQL, $Command));
    }
    function SQL_Assoc($Query)
    {
        return oci_fetch_array($Query, OCI_ASSOC+OCI_RETURN_NULLS));
    }
break;
case 'msaccess': // php-pdo-odbc
$DBName = $conf['DBName'];
$SQL = odbc_connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=$DBName", $conf['DBUser'], $conf['DBPass']);
    if(!$SQL){
        return false;
    }
    function SQL_Query($Command)
    {
        global $SQL;
        return $SQL->query($Command);
    }
    function SQL_Assoc($Query)
    {
        return $Query->fetchall(PDO::FETCH_ASSOC);
    }
break;
case 'mssql': // php-sqlsrv
$Host = $conf['DBHost'];
$Port = $conf['DBPort'];
$SQL = sqlsrv_connect("$Host, $Port", ['Database' => $conf['DBName'], 'UID' => $conf['DBUser'], 'PWD' => $conf['DBPass']]);
    if(!$SQL){
        return false;
    }
    function SQL_Query($Command)
    {
        global $SQL;
        return sqlsrv_query($SQL, $Command);
    }
    function SQL_Assoc($Query)
    {
        return sqlsrv_fetch_array( $Query, SQLSRV_FETCH_ASSOC);
    }
break;
case 'db2': // ibm_db2
$Host = $conf['DBHost'];
$Port = $conf['DBPort'];
$dsn = 'DATABASE='.$conf['DBName'].';HOSTNAME='.$conf['DBHost'].';PORT='.$conf['DBPort'].';PROTOCOL=TCPIP;UID='.$conf['DBUser'].';PWD='.$conf['DBPass'].';';
$SQL = db2_connect($dsn, '', '', $conf['DBOptions']);
    if(!$SQL){
        return false;
    }
    function SQL_Query($Command)
    {
        global $SQL;
        return db2_exec($SQL, $Command);
    }
    function SQL_Assoc($Query)
    {
        return db2_fetch_both($Query);
    }
break;
default:
}
?>
