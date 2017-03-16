<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/*
 * ____________________________________________________________
 *
 * Copyright (C) 2016 NICE IT&T
 *
 * Please do not modify this module.
 * This module may used as it is, there is no warranty.
 *
 * @ description : PHP SSL Client module.
 * @ name        : NicepayLite.php
 * @ author      : NICEPAY I&T (tech@nicepay.co.kr)
 * @ date        : 
 * @ modify      : 22.02.2016
 *
 * 2016.02.22 Update Log
 *
 * ____________________________________________________________
 */
class NicepayLogger {
    public $handle;
    public $type;
    public $log;
    public $debug_mode;
    public $array_key;
    public $debug_msg;
    public $starttime;

    function NicepayLog($log, $mode)
    {
        $this->debug_msg = array( "", "CRITICAL", "ERROR", "NOTICE", "4", "INFO", "6", "DEBUG", "8"  );
        $this->debug_mode = $mode;
        $this->log = $log;
        $this->starttime=GetMicroTime();
    }
    function StartLog($dir, $mid) 
    {
        if( $this->log == "false" ) return true;

        $logfile = $dir. "/logs/".NICEPAY_PROGRAM."_".$this->type."_".$mid."_".date("ymd").".log";
        $this->handle = fopen( $logfile, "a+" );
        if( !$this->handle )
        {
            return false;
        }
        
        $this->WriteLog("START ".NICEPAY_PROGRAM." ".$this->type." (V".NICEPAY_VERSION."B".BUILDDATE."(OS:".php_uname('s').php_uname('r').",PHP:".phpversion()."))" );
        return true;
    }
    function CloseNicepayLog($msg)
    {
        $laptime=$this->GetMicroTime()-$this->starttime;
        $this->WriteLog( "END ".$this->type." ".$msg ." Laptime:[".round($laptime,3)."sec]" );
        $this->WriteLog("===============================================================" );
        fclose( $this->handle );
    }

    function WriteLog($data) 
    {
        if( !$this->handle || $this->log == "false" ) return;
        $pfx = " [" . date("Y-m-d H:i:s") . "] <" . getmypid() . "> ";
        fwrite( $this->handle, $pfx . $data . "\r\n" );
    }
        
    function GetMicroTime()
    {
        list($usec, $sec) = explode(" ", microtime(true));
        return (float)$usec + (float)$sec;
    }
    function SetTimestamp()
    {
        $m = explode(' ',microtime());
        list($totalSeconds, $extraMilliseconds) = array($m[1], (int)round($m[0]*1000,3));
        return date("Y-m-d H:i:s", $totalSeconds) . ":$extraMilliseconds";
    }
    function SetTimestamp1()
    {
        $m = explode(' ',microtime());
        list($totalSeconds, $extraMilliseconds) = array($m[1], (int)round($m[0]*10000,4));
        return date("ymdHis", $totalSeconds) . "$extraMilliseconds";
    }

}