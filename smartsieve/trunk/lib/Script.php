<?php

class Script {

  var $name;         /* filename of script. */
  var $script;       /* full ascii text of script from server. */
  var $size;         /* size of script in bytes. */
  var $so;           /* boolean: is encoding recognised? ie. not created by SmartSieve. */
  var $mode;         /* basic (GUI) or advanced (direct edit) modes. */
  var $rules;        /* array of sieve rules. */
  var $vacation;     /* vacation settings. */
  var $pcount;       /* highest priority value in ruleset. */
  var $errstr;       /* error text. */

  // class constructor
  function Script ($scriptname) {

	$this->name = $scriptname;
	$this->script = '';
        $this->size = 0;
        $this->so = true;
        $this->mode = 'basic';
	$this->rules = array();
	$this->vacation = array();
	$this->pcount = 0;
        $this->errstr = '';

  }
  // end contructor


  // class functions


  // get sieve script rules for this user
  function retrieveRules () {
    global $HTTP_SESSION_VARS;
    global $managesieve;
    $continuebit = 1;
    $sizebit = 2;
    $anyofbit = 4;
    $keepbit = 8;
    $regexbit = 128;
 
    if (!isset($this->name)){
        $this->errstr = 'retrieveRules: no script name specified';
        return false;
    }
    if (!is_object($managesieve)) {
	$this->errstr = "retrieveRules: no sieve session open";
	return false;
    }
 
    // if script doesn't yet exist, nothing to retrieve. 
    // safe to write to this script file.
    if (!SmartSieve::scriptExists($this->name)) {
        $this->so = true;
        return true;
    }
 
    $resp = $managesieve->getscript($this->name);
 
    if ($resp === false) {
	$this->errstr = 'retrieveRules: failed getting script: ' . $managesieve->getError();
        return false;
    }

    $lines = array();
    $lines = preg_split("/\n/",$resp['raw']); //,PREG_SPLIT_NO_EMPTY);
 
    $rules = array();
    $vacation = array();
    $regexps = array('^ *##PSEUDO','^ *#rule','^ *#vacation','^ *#mode');

    /* first line should be the recognised encoded head. if not, the script 
     * is of an unrecognised format. We will view this in direct edit mode. */
    $line = array_shift($lines);
    if (!preg_match("/^# ?Mail(.*)rules for/", $line)){
        $this->so = false;
        $this->mode = 'advanced';
    }
    else
        $this->so = true;
 
    $line = array_shift($lines);
    while (isset($line)){
        foreach ($regexps as $regexp){
            if (preg_match("/$regexp/i",$line)){
                $line = rtrim($line);
                if (preg_match(
        "/^ *#rule&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)$/i",                                $line,$bits)){
                    $rule = array();
                    $rule['priority'] = $bits[1];
                    $rule['status'] = $bits[2];
                    $rule['from'] = $bits[3];
                    $rule['to'] = $bits[4];
                    $rule['subject'] = $bits[5];
                    $rule['action'] = $bits[6];
                    $rule['action_arg'] = $bits[7];
		    // <crnl>s will be encoded as \\n. undo this.
		    $rule['action_arg'] = preg_replace("/\\\\n/","\r\n",$rule['action_arg']);
                    $rule['flg'] = $bits[8];   // bitwise flag
                    $rule['field'] = $bits[9];
                    $rule['field_val'] = $bits[10];
                    $rule['size'] = $bits[11];
                    $rule['continue'] = ($bits[8] & $continuebit);
                    $rule['gthan'] = ($bits[8] & $sizebit); // use 'greater than'
                    $rule['anyof'] = ($bits[8] & $anyofbit);
                    $rule['keep'] = ($bits[8] & $keepbit);
                    $rule['regexp'] = ($bits[8] & $regexbit);
                    $rule['unconditional'] = 0;
                    if (!$rule['from'] && !$rule['to'] && !$rule['subject'] &&
                        !$rule['field'] && !$rule['size'] && $rule['action'] &&
                        !($rule['action'] == 'custom' && preg_match("/^ *(els)?if/i", $rule['action_arg']))) {
                        $rule['unconditional'] = 1;
                    }
 
                    array_push($rules,$rule);
		    if ($rule['priority'] > $this->pcount)
			$this->pcount = $rule['priority'];
                }
                if (preg_match("/^ *#vacation&&(.*)&&(.*)&&(.*)&&(.*)/i",$line,$bits)){
                    $vacation['days'] = $bits[1];
                    $vaddresslist = $bits[2];
                    $vaddresslist = preg_replace("/\"|\s/","",$vaddresslist);
                    $vaddresses = array();
                    $vaddresses = preg_split("/,/",$vaddresslist);
                    $vacation['text'] = $bits[3];
		    // <crnl>s will be encoded as \\n. undo this.
		    $vacation['text'] = preg_replace("/\\\\n/","\r\n",$vacation['text']);
                    $vacation['status'] = $bits[4];
                    $vacation['addresses'] = &$vaddresses;
                }
                if (preg_match("/^ *#mode&&(.*)/i",$line,$bits)){
                    if ($bits[1] == 'basic')
                        $this->mode = 'basic';
                    elseif ($bits[1] == 'advanced')
                        $this->mode = 'advanced';
                    else
                        $this->mode = 'advanced';
                }
            }
        }
        $line = array_shift($lines);
    }
 
    $this->script = $resp['raw'];
    $this->size = $resp['size']; 
    $this->rules = $rules;
    $this->vacation = $vacation;
 
    return true;
  }
 
 
  // update and save sieve script
  function updateScript () {
    global $HTTP_SESSION_VARS,$default;
    global $managesieve;
 
    $activerules = 0;
    $regexused = 0;
    $rejectused = 0;
    $vacationused = 0;
    $notifyused = 0;
 
    include_once "$default->lib_dir/version.php";
 
    if (!is_object($managesieve)) {
	$this->errstr = "updateScript: no sieve session open";
        return false;
    }

    // don't overwrite a file if not created by SmartSieve,
    // unless configured to do so.
    if (!$this->so && !$default->allow_write_unrecognised_scripts) {
        $this->errstr = 'updateScript: encoding not recognised: not safe to overwrite ' . $this->name;
        return false;
    }
 
    // lets generate the main body of the script from our rules
 
    $newscriptbody = "";
    $continue = 1;
 
    foreach ($this->rules as $rule){
      $newruletext = "";
 
      // don't print this rule if disabled.
      if ($rule['status'] != 'ENABLED') {
      }
      else {
 
        $activerules = 1;
 
        // conditions
 
        $anyall = "allof";
        if ($rule['anyof']) $anyall = "anyof";
        if ($rule['regexp']) {
            $regexused = 1;
        }
        $started = 0;
 
        if (!$rule['unconditional']) {
            if (!$continue) $newruletext .= "els";
            $newruletext .= "if " . $anyall . " (";
            if ($rule['from']) {
                if (preg_match("/^\s*!/", $rule['from'])){
                    $newruletext .= 'not ';
                    $rule['from'] = preg_replace("/^\s*!/","",$rule['from']);
                }
                $match = ':contains';
                if (preg_match("/\*|\?/", $rule['from']) && 
                    !empty($default->websieve_auto_matches)){
                    $match = ':matches';
                }
                if ($rule['regexp']) $match = ':regex';
                $newruletext .= "address " . $match . " [\"From\"]";
                $newruletext .= " \"" . $rule['from'] . "\"";
                $started = 1;
            }
            if ($rule['to']) {
                if ($started) $newruletext .= ", ";
                if (preg_match("/^\s*!/", $rule['to'])){
                    $newruletext .= 'not ';
                    $rule['to'] = preg_replace("/^\s*!/","",$rule['to']);
                }
                $match = ':contains';
                if (preg_match("/\*|\?/", $rule['to']) && 
                    !empty($default->websieve_auto_matches)){
                    $match = ':matches';
                }
                if ($rule['regexp']) $match = ':regex';
                $newruletext .= "address " . $match . " [\"To\",\"TO\",\"Cc\",\"CC\"]";
                $newruletext .= " \"" . $rule['to'] . "\"";
                $started = 1;
            }
            if ($rule['subject']) {
                if ($started) $newruletext .= ", ";
                if (preg_match("/^\s*!/", $rule['subject'])){
                    $newruletext .= 'not ';
                    $rule['subject'] = preg_replace("/^\s*!/","",$rule['subject']);
                }
                $match = ':contains';
                if (preg_match("/\*|\?/", $rule['subject']) && 
                    !empty($default->websieve_auto_matches)){
                    $match = ':matches';
                }
                if ($rule['regexp']) $match = ':regex';
                $newruletext .= "header " . $match . " \"subject\"";
                $newruletext .= " \"" . $rule['subject'] . "\"";
                $started = 1;
            }
            if ($rule['field'] && $rule['field_val']) {
                if ($started) $newruletext .= ", ";
                if (preg_match("/^\s*!/", $rule['field_val'])){
                    $newruletext .= 'not ';
                    $rule['field_val'] = preg_replace("/^\s*!/","",$rule['field_val']);
                }
                $match = ':contains';
                if (preg_match("/\*|\?/", $rule['field_val']) && 
                    !empty($default->websieve_auto_matches)){
                    $match = ':matches';
                }
                if ($rule['regexp']) $match = ':regex';
                $newruletext .= "header " . $match . " \"" . $rule['field'] . "\"";
                $newruletext .= " \"" . $rule['field_val'] . "\"";
                $started = 1;
            }
            if ($rule['size']) {
                $xthan = " :under ";
                if ($rule['gthan']) $xthan = " :over ";
                if ($started) $newruletext .= ", ";
                $newruletext .= "size " . $xthan . $rule['size'] . "K";
                $started = 1;
            }
 
        }
 
        // actions
 
        if (!$rule['unconditional']) $newruletext .= ") {\n\t";

        if (preg_match("/folder/i",$rule['action'])) {
            $newruletext .= "fileinto \"" . $rule['action_arg'] . "\";";
        }
        if (preg_match("/reject/i",$rule['action'])) {
            $newruletext .= "reject text: \n" . $rule['action_arg'] . "\n.\n;";
            $rejectused = 1;
        }
        if (preg_match("/address/i",$rule['action'])) {
            $newruletext .= "redirect \"" . $rule['action_arg'] . "\";";
        }
        if (preg_match("/discard/i",$rule['action'])) {
            $newruletext .= "discard;";
        }
        if ($rule['keep']) $newruletext .= "\n\tkeep;";
        if (!$rule['unconditional']) $newruletext .= "\n}";

        if (preg_match("/custom/i",$rule['action'])) {
            $newruletext = $rule['action_arg'];
            if (preg_match("/:regex/i",$rule['action_arg']))
                $regexused = 1;
            if (preg_match("/reject/i",$rule['action_arg']))
                $rejectused = 1;
            if (preg_match("/vacation/i",$rule['action_arg']))
                $vacationused = 1;
            if (preg_match("/notify/i", $rule['action_arg'])) {
                $notifyused = 1;
            }
        }
 
        $continue = 0;
        if ($rule['continue']) $continue = 1;
        if ($rule['unconditional']) $continue = 1;
 
        $newscriptbody .= $newruletext . "\n\n";
 
      } // end 'if ! ENABLED'
    }
 
    // vacation rule
 
    if ($this->vacation) {
        $vacation = $this->vacation;
	if (!$vacation['status']) $vacation['status'] = 'on';
	if (!$vacation['text']){
            if (empty($default->vacation_text)){
                $this->errstr = 'updateScript: no vacation message specified';
                return false;
            }
            $vacation['text'] = $default->vacation_text;
        }
        if (!$vacation['days']){
            if (!empty($default->require_vacation_days)){
                if (empty($default->vacation_days)){
                    $this->errstr = 'updateScript: no vacation days value specified';
                    return false;
                }
                $vacation['days'] = $default->vacation_days;
            }
        }

	// filter out invalid addresses.
        if (is_array($vacation['addresses'])){
            $ok_vaddrs = array();
            foreach($vacation['addresses'] as $addr){
                $tokens = explode('@',$addr);
                if (count($tokens) == 2 
                    && $tokens[0] != '' 
                    && strpos($tokens[1],'.') !== false){
                    array_push($ok_vaddrs,$addr);
                }
            }
            $vacation['addresses'] = $ok_vaddrs;
        }

	if ((!is_array($vacation['addresses']) || empty($vacation['addresses'][0])) 
            && !empty($default->require_vacation_addresses)) {
            // If $smartsieve->authz is fully-qualified, use that.
            if (strpos($_SESSION['smartsieve']['authz'],'@') !== false) {
                $vacation['addresses'][] = $_SESSION['smartsieve']['authz'];
            } else {
                if (empty($_SESSION['smartsieve']['maildomain'])){
                    $this->errstr = 'updateScript: no valid vacation addresses supplied';
                    return false;
                }
                $defaultaddr = $_SESSION['smartsieve']['authz'] . '@' . $_SESSION['smartsieve']['maildomain'];
                $vacation['addresses'][] = $defaultaddr;
            }
	}

        if ($vacation['status'] == 'on') {
            $newscriptbody .= "vacation";
            if ($vacation['days']){
                $newscriptbody .= " :days " . $vacation['days'];
            }
            if (!empty($vacation['addresses'])){
                $newscriptbody .= " :addresses [";
                $first = 1;
                foreach ($vacation['addresses'] as $vaddress) {
                    if (!$first) $newscriptbody .= ", ";
                    $newscriptbody .= "\"" . $vaddress . "\"";
                    $first = 0;
                }
                $newscriptbody .= "]";
            }
            $newscriptbody .= " text:\n" . $vacation['text'] . "\n.\n;\n\n";
        }
	// update with any changes.
	$this->vacation = $vacation;
    }
 
    // generate the script head
 
    $newscripthead = "";
    $newscripthead .= "#Mail filter rules for " . $_SESSION['smartsieve']['authz'] . "\n";
    $newscripthead .= '#Generated by ' . $_SESSION['smartsieve']['auth'] . ' using SmartSieve ' . VERSION . ' ' . date($default->script_date_format);
    $newscripthead .= "\n";
 
    $newrequire = '';
    if ($activerules) {
        $newrequire .= "require [\"fileinto\"";
        if ($regexused) $newrequire .= ",\"regex\"";
        if ($rejectused) $newrequire .= ",\"reject\"";
        if ($this->vacation && $this->vacation['status'] == 'on' || $vacationused)
            $newrequire .= ",\"vacation\"";
        if ($notifyused) $newrequire .= ',"notify"';
        $newrequire .= "];\n\n";
    }
    else {
	// no active rules, but might still have an active vacation rule
	if ($this->vacation && $this->vacation['status'] == 'on')
	    $newrequire .= "require [\"vacation\"];\n\n";
    }
	
 
    // generate the encoded script foot
 
    $newscriptfoot = "";
    $pcount = 1;
    $newscriptfoot .= "##PSEUDO script start\n";
    foreach ($this->rules as $rule){
      // only add rule to foot if status != deleted. this is how we delete a rule.
      if ($rule['status'] != 'DELETED') {
	// we need to handle \r\n here.
	$rule['action_arg'] = preg_replace("/\r\n/","\\n",$rule['action_arg']);
	/* reset priority value. note: we only do this
	 * for compatibility with Websieve. */
	$rule['priority'] = $pcount;
        $newscriptfoot .= "#rule&&" . $rule['priority'] . "&&" . $rule['status'] . "&&" . $rule['from'] . "&&" . $rule['to'] . "&&" . $rule['subject'] . "&&" . $rule['action'] .
"&&" . $rule['action_arg'] . "&&" . $rule['flg'] . "&&" . $rule['field'] . "&&" . $rule['field_val'] . "&&" . $rule['size'] . "\n";
	$pcount = $pcount+2;
      }
    }
    if ($this->vacation) {
        $vacation = $this->vacation;
        $newscriptfoot .= "#vacation&&" . $vacation['days'] . "&&";
        $first = 1;
        foreach ($vacation['addresses'] as $address) {
            if (!$first) $newscriptfoot .= ", ";
            $newscriptfoot .= "\"" . $address . "\"";
            $first = 0;
        }
	$vacation['text'] = preg_replace("/\r\n/","\\n",$vacation['text']);
        $newscriptfoot .= "&&" . $vacation['text'] . "&&" . $vacation['status'] . "\n";
    }
    $newscriptfoot .= "#mode&&" . $this->mode . "\n";
 
    $newscript = $newscripthead . $newrequire . $newscriptbody . $newscriptfoot;
    if ($this->mode == 'advanced')
        $newscript = $newscripthead . $this->removeEncoding()  . $newscriptfoot;
    $this->script = $newscript;
 
    $scriptfile = $this->name;
    if (!$managesieve->putScript($scriptfile, $newscript)) {
	$this->errstr = 'updateScript: putscript failed: ' . $managesieve->getError();
        return false;
    }

    if ($this->name === SmartSieve::getActiveScript() &&
        (SmartSieve::getConf('update_activate_script') === true ||
         SmartSieve::getConf('allow_multi_scripts') === false ||
         count(SmartSieve::getScriptList()) === 0)) {
	if (!$managesieve->setActive($this->name)) {
	    $this->errstr = 'updateScript: activatescript failed: ' . $managesieve->getError();
	    return false;
	}
    }

    return true;
  }


/* return Sieve script text with the encoded lines stripped out. */
function removeEncoding ()
{
    global $script;
    $raw = '';
    $encs = array('^ *##PSEUDO','^ *#rule','^ *#vacation','^ *#mode',
                  '^ *# ?Mail(.*)rules for','^ *# ?Created by Websieve',
                  '^ *#Generated (.+) SmartSieve');
    $lines = array();
    $lines = explode("\n", $script->script);
    foreach ($lines as $line){
        foreach ($encs as $enc){
            if (preg_match("/$enc/", $line))
                continue 2;
        }
        $raw .= $line . "\n";
    }
    return $raw;
}


}


?>
