<?php
/**
 * $Id$
 *
 * Copyright 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */


/**
 * Class Script:: implements a sieve script.
 *
 * @author Stephen Grier <stephengrier@users.sourceforge.net>
 * @version $Revision$
 */
class Script {

   /**
    * Name of script.
    * @var string
    * @access public
    */
    var $name = '';

   /**
    * UTF-8 encoded Sieve text.
    * @var string
    * @access public
    */
    var $script;

   /**
    * Script size in bytes.
    * @var integer
    * @access public
    */
    var $size;

   /**
    * Is this a script created by SmartSieve?
    * @var boolean
    * @access public
    */
    var $so = true;

   /**
    * Script mode: basic (GUI) or advanced (direct edit).
    * @var string
    * @access public
    */
    var $mode;

   /**
    * Sieve rules.
    * @var array
    * @access public
    */
    var $rules = array();

   /**
    * Vacation settings.
    * @var array
    * @access public
    */
    var $vacation = array();

   /**
    * Rule priority, current highest.
    * @var integer
    * @access public
    */
    var $pcount;

   /**
    * Error messages.
    * @var string
    * @access public
    */
    var $errstr;

   /**
    * Class constructor.
    *
    * @param string Script name
    * @return void
    */
    function Script($scriptname)
    {
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


    // Class methods.

   /**
    * Get the script content.
    *
    * This will interpret the encoded part of the script if it exists.
    *
    * @return boolean True on success, false on failure
    */
    function retrieveRules()
    {
        global $managesieve;
        $continuebit = 1;
        $sizebit = 2;
        $anyofbit = 4;
        $keepbit = 8;
        $stopbit = 16;
        $regexbit = 128;
 
        if (!isset($this->name)){
            $this->errstr = 'retrieveRules: no script name specified';
            return false;
        }
        if (!is_object($managesieve)) {
            $this->errstr = "retrieveRules: no sieve session open";
            return false;
        }
 
        // If script doesn't yet exist, nothing to retrieve. 
        // This will be a SmartSieve script.
        if (!SmartSieve::scriptExists($this->name)) {
            $this->so = true;
            return true;
        }
 
        $resp = $managesieve->getscript($this->name);
        if ($resp === false) {
            $this->errstr = 'retrieveRules: failed getting script: ' . $managesieve->getError();
            return false;
        }

        // Split on newlines.
        $lines = array();
        $lines = preg_split("/\n/", $resp['raw']);
        $rules = array();
        $vacation = array();

        // If this script was created by SmartSieve or Websieve, the first line
        // will have a recognizable format. If not, the script is of an unrecognised
        // format, and the user will be able to edit it in direct edit mode.
        $line = array_shift($lines);
        if (!preg_match("/^# ?Mail(.*)rules for/", $line)) {
            $this->so = false;
            $this->mode = 'advanced';
        } else {
            $this->so = true;
        }
 
        $line = array_shift($lines);
        while (isset($line)) {
            $line = rtrim($line);
            if (preg_match("/^ *#rule&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)$/i",
                           $line, $bits)) {
                $rule = array();
                $rule['priority'] = $bits[1];
                $rule['status'] = $bits[2];
                $rule['from'] = $this->unescapeChars($bits[3]);
                $rule['to'] = $this->unescapeChars($bits[4]);
                $rule['subject'] = $this->unescapeChars($bits[5]);
                $rule['action'] = $bits[6];
                $rule['action_arg'] = $this->unescapeChars($bits[7]);
                $rule['flg'] = $bits[8];   // bitwise flag
                $rule['field'] = $this->unescapeChars($bits[9]);
                $rule['field_val'] = $this->unescapeChars($bits[10]);
                $rule['size'] = $this->unescapeChars($bits[11]);
                $rule['continue'] = ($bits[8] & $continuebit);
                $rule['gthan'] = ($bits[8] & $sizebit); // use 'greater than'
                $rule['anyof'] = ($bits[8] & $anyofbit);
                $rule['keep'] = ($bits[8] & $keepbit);
                $rule['stop'] = ($bits[8] & $stopbit);
                $rule['regexp'] = ($bits[8] & $regexbit);
                $rule['unconditional'] = 0;
                if ((!$rule['from'] && !$rule['to'] && !$rule['subject'] &&
                   !$rule['field'] && $rule['size'] === '' && 
                   $rule['action'] != 'custom') OR
                   ($rule['action'] == 'custom' && !preg_match("/^ *(els)?if/i", $rule['action_arg']))) {
                    $rule['unconditional'] = 1;
                }
                array_push($rules, $rule);
                if ($rule['priority'] > $this->pcount) {
                    $this->pcount = $rule['priority'];
                }
            }
            if (preg_match("/^ *#vacation&&(.*)&&(.*)&&(.*)&&(.*)/i", $line, $bits)) {
                $vacation['days'] = $bits[1];
                $vaddresslist = $this->unescapeChars($bits[2]);
                $vaddresslist = preg_replace("/\"|\s/","", $vaddresslist);
                $vaddresses = array();
                $vaddresses = preg_split("/,/", $vaddresslist);
                $vacation['text'] = $this->unescapeChars($bits[3]);
                $vacation['status'] = $bits[4];
                $vacation['addresses'] = &$vaddresses;
            }
            if (preg_match("/^ *#mode&&(.*)/i", $line, $bits)) {
                if ($bits[1] == 'basic') {
                    $this->mode = 'basic';
                } elseif ($bits[1] == 'advanced') {
                    $this->mode = 'advanced';
                } else {
                    $this->mode = 'advanced';
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
 
 
   /**
    * Generate and upload the script.
    *
    * @return boolean true on success, false on failure
    */
    function updateScript()
    {
        global $managesieve;
        $activerules = false;
        $regexused = false;
        $rejectused = false;
        $vacationused = false;
        $notifyused = false;
        $imapflagsused = false;

        include_once SmartSieve::getConf('lib_dir', 'lib') . '/version.php';

        if (!is_object($managesieve)) {
            $this->errstr = "updateScript: no sieve session open";
            return false;
        }

        // Don't overwrite a non-SmartSieve script if configured not to.
        if (!$this->so && SmartSieve::getConf('allow_write_unrecognised_scripts') === false) {
            $this->errstr = 'updateScript: encoding not recognised: not safe to overwrite ' . $this->name;
            return false;
        }

        // Generate the sieve content from rules.

        $newscriptbody = '';
        $continue = 1;
 
        foreach ($this->rules as $rule) {
            $newruletext = '';

            // Generate sieve if rule is enabled.
            if ($rule['status'] == 'ENABLED') {
                $activerules = true;
 
                // Conditions
 
                $anyall = "allof";
                if ($rule['anyof']) {
                    $anyall = "anyof";
                }
                if ($rule['regexp']) {
                    $regexused = true;
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
                            SmartSieve::getConf('websieve_auto_matches') === true) {
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
                            SmartSieve::getConf('websieve_auto_matches') === true) {
                            $match = ':matches';
                        }
                        if ($rule['regexp']) $match = ':regex';
                        $newruletext .= "address " . $match . " [\"To\",\"Cc\"]";
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
                            SmartSieve::getConf('websieve_auto_matches') === true) {
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
                            SmartSieve::getConf('websieve_auto_matches') === true) {
                            $match = ':matches';
                        }
                        if ($rule['regexp']) $match = ':regex';
                        $newruletext .= "header " . $match . " \"" . $rule['field'] . "\"";
                        $newruletext .= " \"" . $rule['field_val'] . "\"";
                        $started = 1;
                    }
                    if (isset($rule['size']) && $rule['size'] !== '') {
                        $xthan = " :under ";
                        if ($rule['gthan']) $xthan = " :over ";
                        if ($started) $newruletext .= ", ";
                        $newruletext .= "size " . $xthan . $rule['size'] . "K";
                        $started = 1;
                    }
                    $newruletext .= ") {\n";
                }
 
                // Actions
 
                if ($rule['action'] == 'folder') {
                    $newruletext .= ((!$rule['unconditional']) ? "\t" : '') . "fileinto \"" . $rule['action_arg'] . "\";\n";
                }
                if ($rule['action'] == 'reject') {
                    $newruletext .= ((!$rule['unconditional']) ? "\t" : '') . "reject text: \n" . $rule['action_arg'] . "\n.\n;\n";
                    $rejectused = true;
                }
                if ($rule['action'] == 'address') {
                    $newruletext .= ((!$rule['unconditional']) ? "\t" : '') . "redirect \"" . $rule['action_arg'] . "\";\n";
                }
                if ($rule['action'] == 'discard') {
                    $newruletext .= ((!$rule['unconditional']) ? "\t" : '') . "discard;\n";
                }
                if ($rule['keep']) {
                    $newruletext .= ((!$rule['unconditional']) ? "\t" : '') . "keep;\n";
                }
                if ($rule['stop']) {
                    $newruletext .= ((!$rule['unconditional']) ? "\t" : '') . "stop;\n";
                }
                if (!$rule['unconditional']) {
                    $newruletext .= "}\n";
                }

                if ($rule['action'] == 'custom') {
                    $newruletext = $rule['action_arg'];
                    if (preg_match("/:regex/i",$rule['action_arg'])) {
                        $regexused = true;
                    }
                    if (preg_match("/reject/i",$rule['action_arg'])) {
                        $rejectused = true;
                    }
                    if (preg_match("/vacation/i",$rule['action_arg'])) {
                        $vacationused = true;
                    }
                    if (preg_match("/notify/i", $rule['action_arg'])) {
                        $notifyused = true;
                    }
                    if (preg_match("/(addflag|setflag|removeflag)/i", $rule['action_arg'])) {
                        $imapflagsused = true;
                    }
                }

                $continue = 0;
                if ($rule['continue']) $continue = 1;
                if ($rule['unconditional']) $continue = 1;

                $newscriptbody .= $newruletext . "\n";
 
            } // end 'if ! ENABLED'
        }
 
        // Vacation rule

        if ($this->vacation) {
            $vacation = $this->vacation;
            if (!$vacation['status']) {
                $this->vacation['status'] = 'on';
            }
            if ($vacation['status'] == 'on') {
                $newscriptbody .= "vacation";
                if ($vacation['days']){
                    $newscriptbody .= " :days " . $vacation['days'];
                }
                if (!empty($vacation['addresses'])) {
                    $newscriptbody .= " :addresses [";
                    for ($i=0; $i<count($vacation['addresses']); $i++) {
                        $newscriptbody .= sprintf("%s\"%s\"", ($i != 0) ? ', ' : '',
                                                  $vacation['addresses'][$i]);
                    }
                    $newscriptbody .= "]";
                }
                $newscriptbody .= " text:\n" . $vacation['text'] . "\n.\n;\n\n";
            }
        }
 
        // Generate script header and add a "require" line if needed.
 
        $newscripthead = "";
        $newscripthead .= "#Mail filter rules for " . $_SESSION['smartsieve']['authz'] . "\n";
        $newscripthead .= '#Generated by ' . $_SESSION['smartsieve']['auth'] . ' using SmartSieve ' . VERSION . ' ' . date(SmartSieve::getConf('script_date_format', 'Y/m/d H:i:s'));
        $newscripthead .= "\n";
 
        $newrequire = '';
        if ($activerules) {
            $newrequire .= "require [\"fileinto\"";
            if ($regexused) {
                $newrequire .= ',"regex"';
            } if ($rejectused) {
                $newrequire .= ',"reject"';
            } if ($this->vacation && $this->vacation['status'] == 'on' || $vacationused) {
                $newrequire .= ',"vacation"';
            } if ($notifyused) {
                $newrequire .= ',"notify"';
            } if ($imapflagsused) {
                $newrequire .= ',"imapflags"';
            }
            $newrequire .= "];\n\n";
        }
        // No active rules, but might still have an active vacation rule.
        elseif ($this->vacation && $this->vacation['status'] == 'on') {
            $newrequire .= "require [\"vacation\"];\n\n";
        }
 
        // Generate an encoded version of script content.
 
        $newscriptfoot = "";
        $pcount = 1;
        $newscriptfoot .= "##PSEUDO script start\n";
        foreach ($this->rules as $rule) {
            // Add rule to foot if status != deleted. This is how we delete a rule.
            if ($rule['status'] != 'DELETED') {
                // Reset priority value. Note, we only do this for 
                // compatibility with Websieve. SmartSieve never uses it.
                $rule['priority'] = $pcount;
                $newscriptfoot .= sprintf("#rule&&%s&&%s&&%s&&%s&&%s&&%s&&%s&&%s&&%s&&%s&&%s\n",
                    $rule['priority'], $this->escapeChars($rule['status']), $this->escapeChars($rule['from']),
                    $this->escapeChars($rule['to']), $this->escapeChars($rule['subject']), $rule['action'],
                    $this->escapeChars($rule['action_arg']), $rule['flg'], $this->escapeChars($rule['field']),
                    $this->escapeChars($rule['field_val']), $rule['size']);
                $pcount = $pcount+2;
            }
        }
        if ($this->vacation) {
            $vacation = $this->vacation;
            $newscriptfoot .= sprintf("#vacation&&%s&&", $vacation['days']);
            for ($i=0; $i<count($vacation['addresses']); $i++) {
                $newscriptfoot .= sprintf("%s\"%s\"", ($i != 0) ? ', ' : '',
                    $this->escapeChars($vacation['addresses'][$i]));
            }
            $newscriptfoot .= sprintf("&&%s&&%s\n", $this->escapeChars($vacation['text']),
                                      $vacation['status']);
        }
        $newscriptfoot .= sprintf("#mode&&%s\n", $this->mode);
 
        // Put the script content together.
        $newscript = $newscripthead . $newrequire . $newscriptbody . $newscriptfoot;

        // But if we're in direct edit mode, content comes direct from the user.
        if ($this->mode == 'advanced') {
            $newscript = $newscripthead . $this->removeEncoding()  . $newscriptfoot;
        }

        $this->script = $newscript;
 
        // Upload the updated script.
        $slist = SmartSieve::getScriptList();
        $scriptfile = $this->name;
        if (!$managesieve->putScript($scriptfile, $newscript)) {
            $this->errstr = 'updateScript: putscript failed: ' . $managesieve->getError();
            return false;
        }

        // If this script is not the active script on the server, set it as the 
        // active script if 1) configured to activate when saving changes; 2) if 
        // only allowing user to edit this script, or 3) there are no existing 
        // scripts on the server.
        if ($this->name !== SmartSieve::getActiveScript() &&
            (SmartSieve::getConf('update_activate_script') === true ||
             SmartSieve::getConf('allow_multi_scripts') === false ||
             count($slist) === 0)) {
            if (!$managesieve->setActive($this->name)) {
                $this->errstr = 'updateScript: activatescript failed: ' . $managesieve->getError();
                return false;
            }
        }
        // All went well.
        return true;
    }


   /**
    * Return the sieve script content with any encoded lines stripped out.
    *
    * @return string The script content minus encoded lines
    */
    function removeEncoding()
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

   /**
    * Make a string safe for the encoded index. Replace CRLFs and & chars.
    *
    * @param string $string The string to make safe
    * @return string The safe string
    */
    function escapeChars($string)
    {
        $string = preg_replace("/\r\n/", "\\n", $string);
        $string = preg_replace("/&/", "\&", $string);
        return $string;
    }

   /**
    * Unescape a string made safe by escapeChars().
    *
    * @param string $string The string to unescape
    * @return string The unescaped string
    */
    function unescapeChars($string)
    {
        $string = preg_replace("/\\\\n/", "\r\n", $string);
        $string = preg_replace("/\\\&/", "&", $string);
        return $string;
    }

}

?>
