<?php
# PressDo SQL Core File
# **functions**
# SQL_Query()
# 
#
session_start();
$conf = json_decode(file_get_contents(__DIR__.'/data/global/config.json'), true);
switch($conf['DBType']){
case 'mysql':
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
case 'pgsql':
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
case 'sqlite':
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
default:
}
?>
