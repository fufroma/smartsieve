<?php
/*
 * $Id$
 *
 * Copyright 2002-2004 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */

// Managesieve response states
define ("F_NO", 0);             
define ("F_OK", 1);
define ("F_BYE", 2);
define ("F_DATA", 3);
define ("F_BYTES", 4);
define ("F_EOF", 5);

// Managesieve response codes
define ("RC_QUOTA", 10);
define ("RC_SASL", 20);
define ("RC_REFERRAL", 30);
define ("RC_UNKNOWN", 255);

// Managesieve states
define ("S_NOCONNECTION", 1);
define ("S_CONNECTED", 2);
define ("S_AUTHENTICATED", 3);


/**
 * Class Managesieve is an implementation of the managesieve protocol 
 * for uploading and managing Sieve scripts.
 *
 * References:
 * draft-martin-managesieve-04.txt
 *
 * @author  Stephen Grier <stephengrier@users.sourceforge.net>
 * @version $Revision$
 */
class Managesieve {

   /**
    * The managesieve server to connect to.
    * @var string
    * @access public
    */
    var $server='127.0.0.1';

   /**
    * The port of the managesieve server.
    * @var string
    * @access public
    */
    var $port = '2000';

   /**
    * The user to authenticate as.
    * @var string
    * @access public
    */
    var $auth = '';

   /**
    * The authentication user's password.
    * @var string
    * @access public
    */
    var $passwd = '';

   /**
    * The user to authorize as.
    * @var string
    * @access public
    */
    var $authz;

   /**
    * SASL auth mechanism to use.
    * @var string
    * @access public
    */
    var $sasl_mech;

   /**
    * The capabilities of the server as advertised in response to the 
    * CAPABILITY command.
    * @var array
    * @access private
    */
    var $_capabilities = array();

   /**
    * The file pointer to the open socket.
    * @var resource
    * @access private
    */
    var $_socket;

   /**
    * Timeout state of last socket read.
    * @var boolean
    * @access private
    */
    var $_sock_timed_out = false;

   /**
    * Connection state. One of the S_* contants.
    * @var integer
    * @access private
    */
    var $_state;

   /**
    * An array containing values from the last server response.
    * @var array
    * @public
    */
    var $resp;

   /**
    * Error message.
    * @var string
    * @access private
    */
    var $_errstr = '';


    /**
     * Class constructor. 
     */
    function Managesieve()
    {
        $this->_errstr = '';
        $this->_state = S_NOCONNECTION;
    }


    // class methods

   /**
    * Open a connection to the server and parse server capabilities.
    *
    * @param server string Server to connect to.
    * @param port   string The server port.
    * @param socket_timeout int The socket timeout in seconds.
    * @return true on success, false on failure.
    */
    function open($server, $port, $socket_timeout=2)
    {
        unset($this->resp);
        $this->_errstr = '';

        if (is_resource($this->_socket)){
            $this->_errstr = 'open: socket already open';
            return false;
        }

        if (isset($server)){
            $this->server = $server;
        }
        if (isset($port)){
            $this->port = $port;
        }

        $this->_socket = fsockopen($this->server, $this->port, $errno, $errstr, "60");
        if (!$this->_socket) {
            $this->_errstr = "open: fsockopen failed with $errno: $errstr";
            return false;
        }
        $this->_state = S_CONNECTED;

        socket_set_timeout($this->_socket, $socket_timeout);
        socket_set_blocking($this->_socket, true);

        return $this->parse_capability();
    }


   /**
    * Parse the server's capability response. This might result from an 
    * initial connection to the server, or from the capability command.
    *
    * @return true on success, false on failure.
    */
    function parse_capability()
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)){
            $this->_errstr = 'parse_capability: no server connection';
            return false;
        }

        $said = $this->read();
        if (strstr($said, "timsieved") == false) {
            $this->close();
            $this->_errstr = "parse_capability: bad response from $this->server: $said";
            return false;
        }

        // If response is "IMPLEMENTATION" "Cyrus timsieved..." 
        // server is Cyrus version 2.

        $tokens = explode("\"", $said);

        if ($tokens[1] == "IMPLEMENTATION"){
            $this->_capabilities['implementation'] = $tokens[3];
            while ($this->get_response() == F_DATA){
                $tokens = explode("\"", $this->resp['data']);
                switch ($tokens[1]){
                    case "SASL":
                        // "SASL" "PLAIN DIGEST-MD5"
                        $this->_capabilities['sasl'] = explode(" ", $tokens[3]);
                        break;
                    case "SIEVE":
                        // "SIEVE" "fileinto reject envelope vacation"
                        $this->_capabilities['extensions'] = explode(" ", $tokens[3]);
                        break;
                    case "STARTTLS":
                        $this->_capabilities['starttls'] = true;
                        break;
                    default:
                        $this->_capabilities['unknown_banners'][] = $this->resp['data'];
                        break;
                }
            }
        }
        // elseif response is "Cyrus timsieved v1..." "SASL={PLAIN, LOGIN}"
        // we will assume server is Cyrus v1.
        elseif (strstr($tokens[1], "Cyrus timsieved") == true){
            $this->_capabilities['implementation'] = $tokens[1];
            // $tokens[3] should look like "SASL={PLAIN, LOGIN}"
            $mechstr = substr(strstr($tokens[3], '{'), 1, strlen($tokens[3])-1);
            $this->_capabilities['sasl'] = explode(", ", $mechstr);
// FIXME: OK line here?
        }
        else {
            // unknown version.
            // a bit desperate if we get here.
            $this->_capabilities['implementation'] = $said;
            $this->_capabilities['sasl'] = $said;
	}

// if result is OK?
        return true;
    }


   /**
    * Read a line from socket. We continue reading until we reach a CRLF 
    * or until fread() times out. The socket should be in blocking mode 
    * with timeout as set in $this->open(). We include the CRLF.
    *
    * @return string line read from socket.
    */
    function read ()
    {
        $buffer = '';
        $this->_sock_timed_out = false;

        if (!is_resource($this->_socket)){
            return $buffer;
        }

        /* read one character at a time and add to $buffer. */
        $char = fread($this->_socket,1);
        while ($char != '') {

            $buffer .= $char;

            /* return $buffer if we've reached CRLF. */
            if (substr($buffer, -2) == "\r\n"){
                return $buffer;
            }
            $char = fread($this->_socket,1);
        }
        $this->_sock_timed_out = true;
        return $buffer;
    }


   /**
    * Get and parse a server response line. The value returned will be one of 
    * the RC_* values, reflect a server response of NO, OK, BYE, unspecified 
    * response data, or a socket timeout. Any response code, error or data 
    * will be set in $this->resp[];
    */
    function get_response ()
    {
        unset($this->resp);
        $this->resp = array();
        $this->_errstr = '';

        $line = $this->read();
        $this->resp['raw'] = $line;
        // match NO {34}\r\n...\r\n...
        // {34} is lenth of error message string, including CRLFs.
        // we strip out the CRLFs which separate each error message.
// FIXME: replace preg_match() calls with explode().
        if (preg_match("/^NO \{(\d+)\}\r\n$/",$line, $m)){
            $errstr = $this->read();
            while (strlen($errstr) < $m[1]){
                $errstr .= $this->read();
// FIXME: we should probably strip the CRLFs.
            }
            // read the CRLF which terminates the multiline error string.
            $this->read();
            $this->resp['code'] = RC_UNKNOWN;
            $this->resp['errstr'] = $errstr;
            $this->resp['state'] = F_NO;
            return F_NO;
        }
        // match NO "error msg"
        elseif (preg_match("/^NO \"(.+)\"\r\n$/",$line, $m)){
            $this->resp['code'] = RC_UNKNOWN;
            $this->resp['errstr'] = $m[1];
            $this->resp['state'] = F_NO;
            return F_NO;
// FIXME: we should read any extra response lines, in case there are some.
        }
        // match NO ("QUOTA") "xxxx"
        elseif (preg_match("/^NO \(\"(\w+)\"\) \"(.+?)\"\r\n$/",$line, $m)){
            $rc = $m[1];
            switch ($rc){
                case "QUOTA":
                    $this->resp['code'] = RC_QUOTA;
                    $this->resp['errstr'] = $m[2];
                    break;
                default:
                    $this->resp['code'] = RC_UNKNOWN;
                    $this->resp['errstr'] = "$rc " . $m[2];
                    break;
            }
            $this->resp['state'] = F_NO;
            return F_NO;
        }
        // match NO ("SASL" "authentication failure") "Authentication error"
        elseif (preg_match("/^NO \(\"(.+?)\" \"(.+?)\"\) \"(.+)\"\r\n$/",$line,$m)){
            switch ($m[1]){
                case "SASL":
                    $this->resp['code'] = RC_SASL;
                    $this->resp['code_args'] = $m[2];
                    $this->resp['errstr'] = $m[3];
                    break;
                default:
                    $this->resp['code'] = RC_UNKNOWN;
                    $this->resp['code_args'] = $m[2];
                    $this->resp['errstr'] = $m[3];
                    break;
            }
            $this->resp['state'] = F_NO;
            return F_NO;
        }
        // match OK
        elseif ($line == "OK\r\n"){
            $this->resp['state'] = F_OK;
            $this->resp['code'] = RC_UNKNOWN;
            return F_OK;
        }
        // match OK "Logout Complete"
        elseif (preg_match("/^OK \"(.+)\"\r\n$/",$line,$m)){
            $this->resp['state'] = F_OK;
            $this->resp['code'] = RC_UNKNOWN;
            $this->resp['errstr'] = $m[1];
            return F_OK;
        }
        // match OK (SASL "cnNwYX1ZG...NzmY3MDN==")
        elseif (preg_match("/^OK \((\w+) \"(.+)\"\)\r\n$/",$line,$m)){
            switch ($m[1]){
                case "SASL":
                    $this->resp['code'] = RC_SASL;
                    $this->resp['code_args'] = $m[2];
                    break;
                default:
                    $this->resp['code'] = RC_UNKNOWN;
                    $this->resp['code_args'] = $m[2];
                    break;
            }
            $this->resp['state'] = F_OK;
            return F_OK;
        }
        // match BYE (REFERRAL "server") "Try Remote."
        elseif (preg_match("/^BYE \((.+) \"(.+)\"\) \"(.+)\"\r\n$/",$line, $m)){
            $rc = $m[1];
            switch ($rc){
                case "REFERRAL":
                    $this->resp['code'] = RC_REFERRAL;
                    $this->resp['code_args'] = $m[2];
                    $this->resp['errstr'] = $m[3];
                    break;
                default:
                    $this->resp['code'] = RC_UNKNOWN;
                    $this->resp['code_args'] = $m[2];
                    $this->resp['errstr'] = $m[3];
                    break;
            }
            $this->resp['state'] = F_BYE;
            return F_BYE;
        }
        // match {123}
        elseif (preg_match("/^\{(\d+)\+?\}\r\n$/", $line, $m)){
            $this->resp['size'] = $m[1];
            $this->resp['state'] = F_BYTES;
            return F_BYTES;
        }
        // check for timeout if, for some reason, we've missed result token.
        if ($this->_sock_timed_out == true){
            $this->resp['data'] = $line;
            $this->resp['state'] = F_EOF;
            return F_EOF;
        }
        // else data
        $this->resp['data'] = $line;
        $this->resp['state'] = F_DATA;
        return F_DATA;
    }


   /*
    * Build a message string from a response.
    */
    function response_to_string()
    {
        $msg = '';
        if (empty($this->resp)){
            return $msg;
        }
        $resp = $this->resp;
        if ($resp['state'] == F_OK){
            $msg .= '[' . F_OK . '] ';
            if (isset($resp['code']) && $resp['code'] != RC_UNKNOWN){
                if ($resp['code'] == RC_SASL){
                    $msg .= 'received final SASL response';
                }else{
                    $msg .= 'unknown response code: ' . $resp['raw'];
                }
            }
            if (isset($resp['errstr'])){
                $msg .= $resp['errstr'];
            }
        }
        if ($resp['state'] == F_NO){
            $msg .= '[' . F_NO . '] ';
            if (isset($resp['code']) && $resp['code'] != RC_UNKNOWN){
                if ($resp['code'] == RC_QUOTA){
                    $msg .= 'over quota: ' . $resp['errstr'];
                } elseif ($resp['code'] == RC_SASL) {
                    $msg .= 'SASL error: ' . $resp['code_args'] . ': ' . $resp['errstr'];
                }else{
                    $msg .= 'unknown response code: ' . $resp['raw'];
                }
            }
            elseif (isset($resp['errstr'])){
                $msg .= $resp['errstr'];
            }
        }
        if ($resp['state'] == F_BYE){
            $msg .= 'BYE received: ';
            if (isset($resp['code']) && $resp['code'] != RC_UNKNOWN){
                if ($resp['code'] == RC_REFERRAL){
                    $msg .= "referred to '" . $resp['code_args'] . "': " . $resp['errstr'];
                }else{
                    $msg .= 'unknown response code: ' . $resp['raw'];
                }
            }
        }
        if ($resp['state'] == F_BYTES){
            $msg .= $resp['size'] . ' bytes of data to follow';
        }
        if ($resp['state'] == F_EOF){
            $msg .= 'socket timed out while reading server response';
        }
        if ($resp['state'] == F_DATA){
            $msg .= 'received data';
        }
        if ($msg == ''){
            return $resp['raw'];
        }
        return $msg;
    }


   /**
    * Select which SASL mechanism to use for authentication.
    * The server advertises which mechanisms it will allow. If 
    * none of the mechanisms SmartSieve supports are available, 
    * this will return false, and authentication will fail.
    *
    * @param mech string containing user specified SASL mechanism
    * @return mixed string containing mechanism to use or boolean false
    */
    function _selectSaslMech($mech=null)
    {
        // List of SASL mechanisms this class supports.
        $supported_mechs = array('digest-md5','plain');

        if (!empty($sasl_mech)){
            if (!in_array(strtolower($sasl_mech), $supported_sasl_mechs)) {
                $this->_errstr = "_selectsaslmech: mechanism \"$sasl_mech\" not supported";
                return false;
            }
            $use_mechs = array($sasl_mech);
        } else {
            $use_mechs = $supported_mechs;
        }

        // Return first supported mech available on server.
        foreach ($use_mechs as $use_mech) {
            if (in_array(strtoupper($use_mech), $this->_capabilities['sasl'])) {
                return $use_mech;
            }
        }
        $this->_errstr = "_selectsaslmech: no available mechanisms";
        return false;
    }


   /*
    * Authenticate using the SASL mechanism selected.
    */
    function authenticate($auth, $passwd, $authz=null, $sasl_mech=null)
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)){
            $this->_errstr = 'authenticate: no server connection';
            return false;
        }
        if (empty($auth)){
            $this->_errstr = 'authenticate: no authentication user specified';
            return false;
        }
        $this->auth = $auth;
        if (empty($passwd)){
            $this->_errstr = 'authenticate: no password specified';
            return false;
        }
        if (isset($authz)){
            $this->authz = $authz;
        }
        if (isset($sasl_mech)){
            $this->sasl_mech = $sasl_mech;
        }

        if (!$sasl_mech = $this->_selectSaslMech($this->sasl_mech)){
            return false;
        }
        switch ($sasl_mech){

            case "plain":
                $authstr = $this->authz . "\x00" . $this->auth . "\x00" . $passwd;
                $authstr = base64_encode($authstr);
                $len = strlen($authstr);
                fputs($this->_socket,"AUTHENTICATE \"PLAIN\" \{$len+}\r\n");
                fputs($this->_socket,"$authstr\r\n");

                switch ($this->get_response()){
                    case F_NO:
                        $this->_errstr = "authenticate: authentication failure connecting to $this->server: " . $this->response_to_string();
                        return false;
                        break;
                    case F_OK:
                        $this->_state = S_AUTHENTICATED;
                        return true;
                        break;
                    default:
                        $this->_errstr = "authenticate: bad authentication response from $this->server: " . $this->response_to_string();
                        return false;
                        break;
                }
                break;

            case "digest-md5":
// FIXME: make digest-md5 code use new read(), get_response(), response_to_string().
                // follows rfc2831 for generating the $response to $challenge
                fputs($this->_socket, "AUTHENTICATE \"DIGEST-MD5\"\r\n");
                // $clen is length of server challenge. 
                $this->get_response();
                if ($this->resp['state'] != F_BYTES) {
                    $this->_errstr = 'authenticate: ' . $this->response_to_string();
                    return false;
                }
                $clen = $this->resp['size'];
                // read the challenge. the max length for this is 2048.
                // don't include the CRLF returned by get_response().
                $this->get_response();
                $challenge = substr($this->resp['data'], 0, -2);
                // vars used when building $response_value and $response
                $cnonce = base64_encode(md5(microtime()));
                $ncount = "00000001";
                $qop_value = "auth"; 
                $digest_uri_value = 'sieve/' . $this->server;
                // decode the challenge string
                $result = array();
                $challenge = base64_decode($challenge);
                preg_match("/nonce=\"(.*)\"/U",$challenge, $matches);
                $result['nonce'] = $matches[1];
                preg_match("/realm=\"(.*)\"/U",$challenge, $matches);
                $result['realm'] = $matches[1];
                preg_match("/qop=\"(.*)\"/U",$challenge, $matches);
                $result['qop'] = $matches[1];
                // verify server supports qop=auth 
                $qop = explode(",",$result['qop']);
                if (!in_array($qop_value, $qop)) {
                   // rfc2831: client MUST fail if no qop methods supported
                   return false;
                }
                // build the $response_value
                $string_a1 = utf8_encode($this->auth).":";
                $string_a1 .= utf8_encode($result['realm']).":";
                $string_a1 .= utf8_encode($passwd);
                $string_a1 = pack('H*', md5($string_a1));
                $A1 = $string_a1.":".$result['nonce'].":".$cnonce.":".utf8_encode($this->authz);
                $A1 = md5($A1);
                $A2 = md5("AUTHENTICATE:$digest_uri_value");
                $string_response = $result['nonce'].":".$ncount.":".$cnonce.":".$qop_value;
                $response_value = md5($A1.":".$string_response.":".$A2);
                // build the challenge $response
                $reply = "charset=utf-8,username=\"" . $this->auth . "\",realm=\"" . $result['realm'] . "\",";
                $reply .= "nonce=\"" . $result['nonce'] . "\",nc=$ncount,cnonce=\"" . $cnonce . "\",";
                $reply .= "digest-uri=\"" . $digest_uri_value . "\",response=" . $response_value . ",";
                $reply .= "qop=" . $qop_value . ",authzid=\"" . utf8_encode($this->authz)."\"";
                $response = base64_encode($reply);
                fputs($this->_socket, "\"$response\"\r\n");

                $this->get_response();

                // With SASL v2 the server sends the final response success data within the 
                // data portion of the SASL response code. With SASL v1.x, however, the server 
                // sends this as a separate response and we must send an extra empty request 
                // before the server sends the final SASL response.
                if (!preg_match("/Cyrus timsieved (v1\.1|v2\.\d)/", $this->_capabilities['implementation'])){
                    // Response should be either "{xxx}\r\nresponse string\r\n" or "NO".
                    if ($this->resp['state'] == F_BYTES) {
                        $this->get_response();
                        // put the empty request.
                        fputs($this->_socket, "{0+}\r\n");
                        fputs($this->_socket, "\r\n");
                        $this->get_response();
                    }
                }

                if ($this->resp['state'] == F_NO) {
                    $this->_errstr = "authenticate: authentication failure connecting to $this->server: " . $this->response_to_string();
                    return false;
                }
                elseif ($this->resp['state'] != F_OK) {
                    $this->_errstr = "authenticate: bad authentication response from $this->server: " . $this->response_to_string();
                    return false;
                }
                $this->_state = S_AUTHENTICATED;
                return true;
                break;

            default:
                $this->_errstr = "authenticate: mechanism '" . $selected_mech . "' not supported";
                return false;
                break;

        } //end switch.
    }


   /*
    * Issue the logout command.
    */
    function logout ()
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)){
            $this->_errstr = 'logout: no server connection';
            return false;
        }
        if ($this->_state == S_AUTHENTICATED){
            fputs($this->_socket,"LOGOUT\r\n");
            switch ($this->get_response()){
                case F_OK:
                    $this->_state = S_CONNECTED;
                    return true;
                    break;
                case F_NO:
                    $this->_errstr = 'logout: failed to logout: ' . $this->response_to_string();
                    return false;
                    break;
                default:
                    $this->_errstr = 'logout: bad logout response: ' . $this->response_to_string();
                    return false;
                    break;
            }
        }
        return true;
    }

// FIXME: merge logout() and close()

    /*
     * Close the socket connection.
     */
    function close () 
    {
        unset($this->resp);
        $this->_errstr = '';

        if (is_resource($this->_socket)) {
            if ($this->_state = S_AUTHENTICATED){
                $this->logout();
            }
            if (!fclose($this->_socket)){
                $this->_errstr = "close: failed closing socket to $this->server";
                return false; 
            }
        }
        $this->_socket = false;
        $this->_state = S_NOCONNECTION;
        return true;
    }


    /*
     * Return an array containing the list of sieve scripts on the 
     * server belonging to the authzed user.
     */
    function listscripts ()
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)) {
            $this->_errstr = 'listscripts: no server connection';
            return false;
        }

        $scripts = array();

        fputs($this->_socket,"LISTSCRIPTS\r\n");

        while ($this->get_response() == F_DATA){

            // Cyrus v1 script lines look like '"script*"' with the 
            // asterisk denoting the active script. Cyrus v2 script 
            // lines will look like '"script" ACTIVE' if active.

            $tokens = explode(" ",substr($this->resp['data'],0,-2));
            $tokens[0] = substr($tokens[0],1,-1);

            $active = false;
            // Cyrus v2 active script: "script" ACTIVE
            if (isset($tokens[1]) && $tokens[1] == 'ACTIVE'){
                $active = true;
            }
            // Cyrus v1 active script: "script*"
            if (strstr($tokens[0], -1) == '*'){
                $tokens[0] = substr($tokens[0], 0, -1);
                $active = true;
            }
            $scripts[$tokens[0]] = $active;
        }

        switch ($this->resp['state']){
            case F_OK:
                return $scripts;
                break;
            case F_NO:
                $this->_errstr = 'listscripts: failed: ' . $this->response_to_string();
                return false;
                break;
            default:
                $this->_errstr = 'listscripts: bad response: ' . $this->response_to_string();
                break;
        }
        return false;
    }


    /*
     * Retrieve the contents of Sieve script $scriptname.
     * Return false if the script does not exist.
     */
    function getscript ($scriptname)
    {
        unset($this->resp);
        $this->_errstr = '';

        if (empty($scriptname)) {
            $this->_errstr = "getscript: no script file specified";
            return false;
        }
        if (!is_resource($this->_socket)) {
            $this->_errstr = "getscript: no server connection";
            return false;
        }

        $script = array('raw'=>'','size'=>0);

        fputs($this->_socket,"GETSCRIPT \"$scriptname\"\r\n");

        // if OK 1st line of response should be script length: {123}
        if ($this->get_response() == F_BYTES){
            $script['size'] = $this->resp['size'];
        }else{
            $this->_errstr = "getscript: could not get script \"$scriptname\": " . $this->response_to_string();
            return false;
        }

        $raw = '';
        while ($this->get_response() == F_DATA){
            $raw .= $this->resp['data'];
        }

        if ($this->resp['state'] != F_OK){
            $this->_errstr = "getscript: could not get script \"$scriptname\": " . $this->response_to_string();
            return false;
        }
        // don't include trailing CRLF
        $script['raw'] = substr($raw,0,$script['size']);
        return $script;
    }


    /*
     * Set $scriptname as the active script. If $scrptname is the empty
     * string "", SETACTIVE "" will deactivate any active script.
     */
    function activatescript ($scriptname='')
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)) {
            $this->_errstr = "activatescript: no server connection";
            return false;
        }

        fputs($this->_socket,"SETACTIVE \"$scriptname\"\r\n");

        if ($this->get_response() == F_OK) {
            return true;
        }

        $this->_errstr = "activatescript: could not activate script \"$scriptname\": " . $this->response_to_string();
        return false;
    }


   /*
    * Check that the user will not exceed sieve_maxscriptsize or 
    * sieve_maxscripts by uploading $scriptname of size $size bytes.
    * Note: HAVESPACE is broken in Cyrus up to v2.0.16.
    */
    function havespace ($scriptname='', $size=0)
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)) {
            $this->_errstr = 'havespace: no server connection';
            return false;
        }

        fputs($this->_socket,"HAVESPACE \"$scriptname\" $size\r\n");

        if ($this->get_response() == F_OK) {
            return true;
        }

        $this->_errstr = 'havespace: ' . $this->response_to_string();
        return false;
    }


   /*
    * Submit the script $name containing $text to the server.
    * The script will not be active until we call $this->activatescript().
    * Zero length scripts should be allowed.
    */
    function putscript ($name,$text='')
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)) {
            $this->_errstr = "putscript: no server connection";
            return false;
        }

        $len = strlen($text);
        fputs($this->_socket,"PUTSCRIPT \"$name\" \{$len+}\r\n");
        fputs($this->_socket,"$text\r\n");

        if ($this->get_response() == F_OK){
            return true;
        }

        $this->_errstr = "putscript: could not put script \"$name\": " . $this->response_to_string();
//FIXME: Work-around for bug in Cyrus 2.0.
        /* Work-around for extra response bug in Cyrus 2.0. */
        if ($this->resp['state'] == F_NO && 
            $this->resp['errstr'] == 'Did not specify script data') {
            while ($str = $this->read()) {
                $this->_errstr .= $str;
            }
        } 
        return false;
    }


   /*
    * Delete the script $scriptname.
    */
    function deletescript ($scriptname)
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)) {
            $this->_errstr = "deletescript: no server connection";
            return false;
        }

        fputs($this->_socket,"DELETESCRIPT \"$scriptname\"\r\n");

        if ($this->get_response() == F_OK){
            return true;
        }

        $this->_errstr = "deletescript: could not delete script \"$scriptname\": " . $this->response_to_string();
        return false;
    }


   /**
    * Return the last error string.
    *
    * @return string $this->_errstr
    */
    function getError()
    {
        return $this->_errstr;
    }



} // class Managesieve


?>
