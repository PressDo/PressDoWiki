<?php
# PressDo SQL Core File
# **functions**
# SQL_Query()
# 
#
session_start();
include 'config.php';
if($conf['DBType'] == 'mysql'){
    $SQL = new mysqli($conf['DBHost'],$conf['DBUser'],$conf['DBPass'],$conf['DBName'],$conf['DBPort']);
    $SQL->set_charset("utf8");
    if(!$SQL){
        return false;
    }
    function SQL_Query($Command)
    {
        global $SQL;
        $q = $SQL->query($Command);
        return array(mysqli_fetch_assoc($q), $q->num_rows);
    }
}elseif($conf['DBType'] == 'pgsql'){
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
        return array(pg_fetch_assoc($q), pg_num_rows($q));
    }
}
?>