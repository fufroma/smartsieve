<?php
/*
 * $Id$
 *
 * Copyright 2002 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


class Log {

    // class constructor
    function Log($method,$facility,$ident) {

	$this->method = $method;
	$this->facility = $facility;
	$this->ident = $ident;
	$this->open = false;

	if ($method == 'file'){
	    if (!$this->open_file()) return false;
	}
	elseif ($method == 'syslog'){
	    if (!$this->open_syslog()) return false;
	}
	else {
	    $this->errstr = "Log: invalid log method: $method";
	    return false;
	}

	return true;
    }

    function open_file () {

	if (!$this->open){
	    if (!$this->stream = fopen($this->facility,'a')){
		$this->errstr = "open_file: can't open logfile $facility";
		return false;
	    }
	    $this->open = true;
	}
	return true;
    }

    function open_syslog () {

	if (!$this->open){
	    if (!$this->stream = openlog($this->ident, LOG_PID, 
						$this->facility)){
		$this->errstr = "open_syslog: can't open syslog $facility";
		return false;
	    }
            $this->open = true;
        }
	return true;
    }

    function writeToLog($msg,$level) {
	if (!isset($this->stream)){
	    $this->errstr = "writeToLog: no log open";
	    return false;
	}
	$s = "$msg\n";
	if ($this->method == 'file'){
	    $s = strftime("%b %d %T") . ' [' . $this->ident . "] $s";
	    $ret = fwrite($this->stream, $s);
	    if ($ret == -1){
	        $this->errstr = "writeToLog: error writing to log";
	        return false;
	    }
	}
	elseif ($this->method == 'syslog'){
	    if (!syslog($level, $s)){
		$this->errstr = "writeToLog: error writing to log";
		return false;
	    }
	}
	return true;
    }

}


?>
