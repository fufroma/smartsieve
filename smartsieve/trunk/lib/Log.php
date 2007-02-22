<?php
/*
 * $Id$
 *
 * Copyright (C) 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


class Log {

    var $method;   /* logging method: file or syslog. */
    var $facility; /* syslog facility or file name, depending on $method. */
    var $ident;    /* string to identify SmartSieve log messages in log. */
    var $stream;   /* file pointer to log facility. */
    var $open;     /* boolean: is fp to log open? */
    var $errstr;   /* error messages. */

    // class constructor
    function Log($method,$facility,$ident) {

	$this->method = $method;
	$this->facility = $facility;
	$this->ident = $ident;
        $this->stream = 0;
	$this->open = false;
        $this->errstr = '';

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
	if (!$this->stream){
	    $this->errstr = "writeToLog: no log open";
	    return false;
	}
	if ($this->method == 'file'){
	    $s = strftime("%b %d %T") . ' [' . $this->ident . "] $msg\n";
	    if (!fwrite($this->stream, $s)){
	        $this->errstr = "writeToLog: error writing to log";
	        return false;
	    }
	}
	elseif ($this->method == 'syslog'){
	    if (!syslog($level, $msg)){
		$this->errstr = "writeToLog: error writing to log";
		return false;
	    }
	}
	return true;
    }

}


?>
