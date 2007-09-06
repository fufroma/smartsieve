<?php
/**
 * $Id$
 *
 * Copyright (C) 2002-2007 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */

// Client applications.
define("CLIENT_UNKNOWN", "unknown");
define("CLIENT_SMARTSIEVE", "smartsieve");
define("CLIENT_WEBSIEVE", "websieve");

// Test types.
define ("TEST_ADDRESS", "address");
define ("TEST_HEADER", "header");
define ("TEST_SIZE", "size");
define ("TEST_BODY", "body");

// Action types.
define ("ACTION_FILEINTO", "fileinto");
define ("ACTION_REDIRECT", "redirect");
define ("ACTION_REJECT", "reject");
define ("ACTION_KEEP", "keep");
define ("ACTION_DISCARD", "discard");
define ("ACTION_CUSTOM", "custom");
define ("ACTION_VACATION", "vacation");
define ("ACTION_ADDFLAG", "addflag");
define ("ACTION_NOTIFY", "notify");
define ("ACTION_STOP", "stop");

// Match types.
define ("MATCH_IS", ":is");
define ("MATCH_CONTAINS", ":contains");
define ("MATCH_MATCHES", ":matches");
define ("MATCH_REGEX", ":regex");

// Controls.
define ("CONTROL_IF", "if");
define ("CONTROL_ELSEIF", "elseif");
define ("CONTROL_ELSE", "else");

// Bitwise flags.
define ("CONTINUE_BIT", 1);
define ("SIZE_BIT", 2);
define ("ANYOF_BIT", 4);
define ("KEEP_BIT", 8);
define ("STOP_BIT", 16);
define ("REGEX_BIT", 128);

// Special rule identifiers.
define("RULE_TAG_FORWARD", 'forward');
define("RULE_TAG_SPAM", 'spam');
define("RULE_TAG_WHITELIST", 'whitelist');
define("RULE_TAG_VACATION", 'vacation');


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
    var $content;

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
    * Client application that wrote this script.
    * @var string
    * @access public
    */
    var $client = CLIENT_UNKNOWN;

   /**
    * Version of client that wrote this script.
    * @var array
    * @access public
    */
    var $version = null;

   /**
    * Sieve extensions used.
    * @var array
    * @access public
    */
    var $extensions = array();

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
        $this->content = '';
        $this->size = 0;
        $this->so = true;
        $this->mode = 'basic';
        $this->rules = array();
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
    function getContent()
    {
        global $managesieve;
 
        if (!isset($this->name)){
            $this->errstr = 'getContent: no script name specified';
            return false;
        }
        if (!is_object($managesieve)) {
            $this->errstr = "getContent: no sieve session open";
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
            $this->errstr = 'getContent: failed getting script: ' . $managesieve->getError();
            return false;
        }

        // Split on newlines.
        $lines = array();
        $lines = preg_split("/\n/", $resp['raw']);
        $this->rules = array();

        // If this script was created by SmartSieve or Websieve, the first line
        // will have a recognizable format. If not, the script is of an unrecognised
        // format, and the user will be able to edit it in direct edit mode.
        $line = array_shift($lines);
        if (!preg_match("/^# ?Mail(.*)rules for/", $line)) {
            $this->so = false;
            $this->mode = 'advanced';
        } else {
            $line = array_shift($lines);
            if (preg_match("/^#Generated by ([^ ]+) using SmartSieve ([0-9])\.([0-9])\.([0-9])(-([^ ]+))? .+$/", $line, $m)) {
                $this->client = CLIENT_SMARTSIEVE;
                $this->version = array('major'=>$m[2], 'minor'=>$m[3], 'bugfix'=>$m[4],
                                       'tag'=>(isset($m[6])) ? $m[6] : null);
                $this->so = true;
            } elseif (preg_match("/^# Created by Websieve version ([0-9])\.([0-9]{1,2})([a-z])?$/", $line, $m)) {
                $this->client = CLIENT_WEBSIEVE;
                $this->version = array('major'=>$m[1], 'minor'=>$m[2], 'bugfix'=>null,
                                       'tag'=>(isset($m[3])) ? $m[3] : null);
                $this->so = true;
            } else {
                $this->so = false;
                $this->mode = 'advanced';
            }
        }
 
        $line = array_shift($lines);
        $startNewBlock = false;
        while (isset($line)) {
            $line = rtrim($line);
            if (substr($line, 0, 18) == '#SmartSieveRule#a:') {
                $serialized = $this->unescapeChars(substr($line, 16));
                $this->rules[] = unserialize($serialized);
            }
            // Legacy metadata format.
            elseif (preg_match("/^ *#rule&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)&&(.*)$/i",
                           $line, $bits)) {
                $rule = array();
                $priority = $bits[1]; // Ignored.
                $rule['status'] = $bits[2];
                $rule['conditions'] = array();
                $rule['actions'] = array();
                $from = $this->unescapeChars($bits[3]);
                if (!empty($from)) {
                    $condition = array();
                    $condition['type'] = TEST_ADDRESS;
                    $condition['header'] = 'from';
                    if (preg_match("/^\s*!/", $from)) {
                        $condition['not'] = true;
                        preg_replace("/^\s*!/", '', $from);
                    }
                    $condition['matchStr'] = $from;
                    if (($bits[8] & REGEX_BIT)) {
                        $condition['matchType'] = MATCH_REGEX;
                    } elseif (preg_match("/\*|\?/", $condition['matchStr']) &&
                        SmartSieve::getConf('websieve_auto_matches') === true) {
                        $condition['matchType'] = MATCH_MATCHES;
                    } else {
                        $condition['matchType'] = MATCH_CONTAINS;
                    }
                    $rule['conditions'][] = $condition;
                }
                $to = $this->unescapeChars($bits[4]);
                if (!empty($to)) {
                    $condition = array();
                    $condition['type'] = TEST_ADDRESS;
                    $condition['header'] = array('to', 'cc');
                    if (preg_match("/^\s*!/", $to)) {
                        $condition['not'] = true;
                        preg_replace("/^\s*!/", '', $to);
                    }
                    $condition['matchStr'] = $to;
                    if (($bits[8] & REGEX_BIT)) {
                        $condition['matchType'] = MATCH_REGEX;
                    } elseif (preg_match("/\*|\?/", $condition['matchStr']) &&
                        SmartSieve::getConf('websieve_auto_matches') === true) {
                        $condition['matchType'] = MATCH_MATCHES;
                    } else {
                        $condition['matchType'] = MATCH_CONTAINS;
                    }
                    $rule['conditions'][] = $condition;
                }
                $subject = $this->unescapeChars($bits[5]);
                if (!empty($subject)) {
                    $condition = array();
                    $condition['type'] = TEST_HEADER;
                    $condition['header'] = 'subject';
                    if (preg_match("/^\s*!/", $subject)) {
                        $condition['not'] = true;
                        preg_replace("/^\s*!/", '', $subject);
                    }
                    $condition['matchStr'] = $subject;
                    if (($bits[8] & REGEX_BIT)) {
                        $condition['matchType'] = MATCH_REGEX;
                    } elseif (preg_match("/\*|\?/", $condition['matchStr']) &&
                        SmartSieve::getConf('websieve_auto_matches') === true) {
                        $condition['matchType'] = MATCH_MATCHES;
                    } else {
                        $condition['matchType'] = MATCH_CONTAINS;
                    }
                    $rule['conditions'][] = $condition;
                }
                $header = $this->unescapeChars($bits[9]);
                $headerMatchStr = $this->unescapeChars($bits[10]);
                if (!empty($header)) {
                    $condition = array();
                    $condition['type'] = TEST_HEADER;
                    $condition['header'] = $header;
                    $condition['matchStr'] = $headerMatchStr;
                    if (preg_match("/^\s*!/", $headerMatchStr)) {
                        $condition['not'] = true;
                        preg_replace("/^\s*!/", '', $headerMatchStr);
                    }
                    if (($bits[8] & REGEX_BIT)) {
                        $condition['matchType'] = MATCH_REGEX;
                    } elseif (preg_match("/\*|\?/", $condition['matchStr']) &&
                        SmartSieve::getConf('websieve_auto_matches') === true) {
                        $condition['matchType'] = MATCH_MATCHES;
                    } else {
                        $condition['matchType'] = MATCH_CONTAINS;
                    }
                    $rule['conditions'][] = $condition;
                }
                $size = $this->unescapeChars($bits[11]);
                if (!empty($size)) {
                    $condition = array();
                    $condition['type'] = TEST_SIZE;
                    $condition['kbytes'] = $size;
                    $condition['gthan'] = ($bits[8] & SIZE_BIT);
                    $rule['conditions'][] = $condition;
                }
                $actionType = $this->unescapeChars($bits[6]);
                $actionArg = $this->unescapeChars($bits[7]);
                if (!empty($actionType)) {
                    $action = array();
                    switch ($actionType) {
                        case ('folder'):
                            $action['type'] = ACTION_FILEINTO;
                            $action['folder'] = $actionArg;
                            break;
                        case ('address'):
                            $action['type'] = ACTION_REDIRECT;
                            $action['address'] = $actionArg;
                            break;
                        case ('reject'):
                            $action['type'] = ACTION_REJECT;
                            $action['message'] = $actionArg;
                            break;
                        case ('discard'):
                            $action['type'] = ACTION_DISCARD;
                            break;
                        case ('custom'):
                            $action['type'] = ACTION_CUSTOM;
                            $action['sieve'] = $actionArg;
                            break;
                    }
                    $rule['actions'][] = $action;
                }
                if ($bits[8] & KEEP_BIT) {
                    $action = array();
                    $action['type'] = ACTION_KEEP;
                    $rule['actions'][] = $action;
                }
                if ($bits[8] & STOP_BIT) {
                    $action = array();
                    $action['type'] = ACTION_STOP;
                    $rule['actions'][] = $action;
                }

                $rule['control'] = ($startNewBlock) ? CONTROL_IF : CONTROL_ELSEIF;
                if ($rule['status'] == 'ENABLED') {
                    $startNewBlock = ($bits[8] & CONTINUE_BIT);
                }
                $rule['matchAny'] = ($bits[8] & ANYOF_BIT);
                // Is this a spacial forward rule?
                if ($this->hasCondition($rule) === false &&
                    count($rule['actions']) > 0 &&
                    $rule['actions'][0]['type'] == ACTION_REDIRECT &&
                    $this->getSpecialRuleId(RULE_TAG_FORWARD) === null) {
                    $rule['special'] = RULE_TAG_FORWARD;
                }
                $this->rules[] = $rule;
            }
            // Legacy vacation values.
            elseif (preg_match("/^ *#vacation&&(.*)&&(.*)&&(.*)&&(.*)/i", $line, $bits)) {
                $rule = array();
                $rule['status'] = ($bits[4] == 'on') ? 'ENABLED' : 'DISABLED';
                $rule['conditions'] = array();
                $rule['actions'] = array();
                $action = array();
                $action['type'] = ACTION_VACATION;
                $action['days'] = $bits[1];
                $action['message'] = $this->unescapeChars($bits[3]);
                $vaddresslist = $this->unescapeChars($bits[2]);
                $vaddresslist = preg_replace("/\"|\s/","", $vaddresslist);
                $action['addresses'] = preg_split("/,/", $vaddresslist);
                $rule['actions'][] = $action;
                $rule['control'] = ($startNewBlock) ? CONTROL_IF : CONTROL_ELSEIF;
                $rule['matchAny'] = 0;
                $rule['special'] = RULE_TAG_VACATION;
                $this->rules[] = $rule;
            }
            elseif (preg_match("/^ *#mode&&(.*)/i", $line, $bits)) {
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
        $this->content = $resp['raw'];
        $this->size = $resp['size']; 
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
 
        foreach ($this->rules as $rule) {
            $newscriptbody .= $this->getSieveForRule($rule);
        }
 
        // Generate script header and add a "require" line if needed.
 
        $newscripthead = sprintf("#Mail filter rules for %s\n", $_SESSION['smartsieve']['authz']);
        $newscripthead .= sprintf("#Generated by %s using SmartSieve %s %s\n", $_SESSION['smartsieve']['auth'],
                                  VERSION, date(SmartSieve::getConf('script_date_format', 'Y/m/d H:i:s')));
 
        $newrequire = '';
        $started = false;
        foreach ($this->extensions as $ext=>$used) {
            if ($used == true) {
                $newrequire .= ($started) ? ',' : 'require [';
                $newrequire .= sprintf("\"%s\"", $ext);
                $started = true;
            }
        }
        $newrequire .= (strlen($newrequire) > 0) ? "];\n\n" : '';
 
        // Generate an encoded version of script content.
 
        $newscriptfoot = "##PSEUDO script start\n";
        foreach ($this->rules as $rule) {
            // Add rule to foot if status != deleted. This is how we delete a rule.
            if ($rule['status'] != 'DELETED') {
                $newscriptfoot .= '#SmartSieveRule#' . $this->escapeChars(serialize($rule)) . "\n";
            }
        }
        $newscriptfoot .= sprintf("#mode&&%s\n", $this->mode);
 
        // Put the script content together.
        $newscript = $newscripthead . $newrequire . $newscriptbody . $newscriptfoot;

        // But if we're in direct edit mode, content comes direct from the user.
        if ($this->mode == 'advanced') {
            $newscript = $newscripthead . $this->removeEncoding()  . $newscriptfoot;
        }

        // Upload the updated script.
        $slist = SmartSieve::getScriptList();
        if (!$managesieve->putScript($this->name, $newscript)) {
            $this->errstr = 'updateScript: putscript failed: ' . $managesieve->getError();
            return false;
        }
        $this->content = $newscript;

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
    * Generate sieve for a filter rule.
    *
    * @param array $rule The rule to generate sieve from
    * @return string The sieve for the rule
    */
    function getSieveForRule($rule)
    {
        $newruletext = '';
        static $startNewBlock = true;

        // Generate sieve if rule is enabled.
        if (!empty($rule) && $rule['status'] == 'ENABLED') {

            // Conditions

            $started = false;

            if ($this->hasCondition($rule)) {
                $newruletext .= sprintf("%sif %s (",
                    ($startNewBlock || $rule['control'] == CONTROL_IF) ? '' : 'els',
                    ($rule['matchAny']) ? 'anyof' : 'allof');
                foreach ($rule['conditions'] as $condition) {
                    $newruletext .= ($started) ? ', ' : '';
                    if ($condition['type'] == TEST_ADDRESS) {
                        if ($condition['matchType'] == MATCH_REGEX) {
                            $this->extensions['regex'] = true;
                        }
                        $newruletext .= sprintf("%saddress %s %s \"%s\"",
                            (!empty($condition['not'])) ? 'not ' : '',
                            $condition['matchType'],
                            (is_array($condition['header'])) ? sprintf('["%s"]', implode('","', $condition['header'])) : sprintf('"%s"', $condition['header']),
                            $condition['matchStr']);
                    } elseif ($condition['type'] == TEST_HEADER) {
                        if ($condition['matchType'] == MATCH_REGEX) {
                            $this->extensions['regex'] = true;
                        }
                        $newruletext .= sprintf("%sheader %s \"%s\" \"%s\"",
                            (!empty($condition['not'])) ? 'not ' : '',
                            $condition['matchType'], $condition['header'], $condition['matchStr']);
                    } elseif ($condition['type'] == TEST_SIZE) {
                        $newruletext .= sprintf("size %s %sK",
                            ($condition['gthan']) ? ':over' : ':under', $condition['kbytes']);
                    } elseif ($condition['type'] == TEST_BODY) {
                        $this->extensions['body'] = true;
                        if ($condition['matchType'] == MATCH_REGEX) {
                            $this->extensions['regex'] = true;
                        }
                        $newruletext .= sprintf("%sbody %s %s\"%s\"",
                                (!empty($condition['not'])) ? 'not ' : '',
                                $condition['matchType'],
                                (!empty($condition['transform'])) ? sprintf("%s ", $condition['transform']) : '',
                                $condition['matchStr']);
                    }
                    $started = true;
                }
                $newruletext .= ") {\n";
            }

            // Actions

            $custom = false;
            foreach ($rule['actions'] as $action) {
                switch ($action['type']) {
                    case (ACTION_FILEINTO):
                        $newruletext .= sprintf("%sfileinto \"%s\";\n",
                                                $this->hasCondition($rule) ? "\t" : '',
                                                $action['folder']);
                        $this->extensions['fileinto'] = true;
                        break;
                    case (ACTION_REJECT):
                        $newruletext .= sprintf("%sreject text:\n%s\n.\n;\n",
                                                $this->hasCondition($rule) ? "\t" : '',
                                                $action['message']);
                        $this->extensions['reject'] = true;
                        break;
                    case (ACTION_REDIRECT):
                        $newruletext .= sprintf("%sredirect \"%s\";\n",
                                                $this->hasCondition($rule) ? "\t" : '',
                                                $action['address']);
                        break;
                    case (ACTION_DISCARD):
                        $newruletext .= sprintf("%sdiscard;\n",
                                                $this->hasCondition($rule) ? "\t" : '');
                        break;
                    case (ACTION_KEEP):
                        $newruletext .= sprintf("%skeep;\n",
                                                $this->hasCondition($rule) ? "\t" : '');
                        break;
                    case (ACTION_STOP):
                        $newruletext .= sprintf("%sstop;\n",
                                                $this->hasCondition($rule) ? "\t" : '');
                        break;
                    case (ACTION_VACATION):
                        $addstr = '';
                        if (!empty($action['addresses'])) {
                            $addstr .= ':addresses [';
                            for ($i=0; $i<count($action['addresses']); $i++) {
                                $addstr .= sprintf("%s\"%s\"", ($i != 0) ? ', ' : '',
                                                  $action['addresses'][$i]);
                            }
                            $addstr .= '] ';
                        }
                        $newruletext .= sprintf("%svacation %s%stext:\n%s\n.\n;\n\n",
                            $this->hasCondition($rule) ? "\t" : '',
                            (!empty($action['days'])) ? sprintf(":days %s ", $action['days']) : '',
                            (!empty($addstr)) ? $addstr : '', $action['message']);
                        $this->extensions['vacation'] = true;
                        break;
                    case (ACTION_ADDFLAG):
                        $newruletext .= sprintf("%saddflag \"%s\";\n",
                                                $this->hasCondition($rule) ? "\t" : '',
                                                $action['flag']);
                        $this->extensions['imapflags'] = true;
                        break;
                    case (ACTION_NOTIFY):
                        $newruletext .= sprintf("%snotify :method \"%s\" :options \"%s\" :message \"%s\";\n",
                                                $this->hasCondition($rule) ? "\t" : '',
                                                $action['method'], $action['options'], $action['message']);
                        $this->extensions['notify'] = true;
                        break;
                    case (ACTION_CUSTOM):
                        // Scrap the above and just display the custom text.
                        $newruletext = $action['sieve'];
                        $custom = true;
                        if (stripos($action['sieve'], ':regex') !== false) {
                            $this->extensions['regex'] = true;
                        } if (stripos($action['sieve'], 'reject') !== false) {
                            $this->extensions['reject'] = true;
                        } if (stripos($action['sieve'], 'vacation') !== false) {
                            $this->extensions['vacation'] = true;
                        } if (stripos($action['sieve'], 'notify') !== false) {
                            $this->extensions['notify'] = true;
                        } if (stripos($action['sieve'], 'addflag') !== false ||
                              stripos($action['sieve'], 'setflag') !== false ||
                              stripos($action['sieve'], 'removeflag') !== false) {
                            $this->extensions['imapflags'] = true;
                        }
                        continue 2;
                        break;
                }
            }

            if ($this->hasCondition($rule) && $custom == false) {
                $newruletext .= "}\n";
            }
            $newruletext .= "\n";

            // Should next rule start with an "if..."?
            $startNewBlock = false;
            if ($this->hasCondition($rule) == false) {
                $startNewBlock = true;
            }

        }
        return $newruletext;
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
        $lines = explode("\n", $script->content);
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
        $string = preg_replace('/\\\\/', '\\\\\\', $string);
        $string = preg_replace("/\r\n/", "\\n", $string);
        $string = preg_replace("/&/", "\&", $string);
        $string = preg_replace("/\|/", "\|", $string);
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
        // 1.0.0-RC2 and newer escape '\' and '|'.
        if ($this->checkVersion(1, 0, 0, 'RC2') >= 0) {
            $string = preg_replace("/\\\\\|/", "|", $string);
            $string = preg_replace('/\\\\\\\\/', '\\', $string);
        }
        return $string;
    }

   /**
    * Check what version of SmartSieve this script was created by.
    *
    * @param integer $major Major version number
    * @param integer $minor Minor version number
    * @param integer $bugfix Bugfix version number
    * @param string $tag, Version tag
    * @return integer < 0 if version is earlier, 0 if the same, or > 0 if newer
    */
    function checkVersion($major=null, $minor=null, $bugfix=null, $tag=null)
    {
        // If called without parameters use current version.
        if (is_null($major)) {
            include_once SmartSieve::getConf('lib_dir', 'lib') . '/version.php';
            list($major, $minor, $bugfix) = explode('.', VERSION);
            if (strpos($bugfix, '-') !== false) {
                list($bugfix, $tag) = explode('-', $bugfix);
            }
        }
        $scriptVer = sprintf("%s%s%s%s",
                             (isset($this->version['major'])) ? $this->version['major'] : '',
                             (isset($this->version['minor'])) ? $this->version['minor'] : '',
                             (isset($this->version['bugfix'])) ? $this->version['bugfix'] : '',
                             (isset($this->version['tag'])) ? $this->version['tag'] : 'ZZZ');
        $checkVer = sprintf("%s%s%s%s", $major, $minor, $bugfix, (isset($tag)) ? $tag : 'ZZZ');
        return strcmp($scriptVer, $checkVer);
    }

   /**
    * Does rule have a condition.
    *
    * @param array $rule The rule to check
    * @return boolean True if rule has a condition, false if not
    */
    function hasCondition($rule)
    {
        $custom = null;
        // A custom rule might have a condition in it.
        foreach ($rule['actions'] as $action) {
            if ($action['type'] == ACTION_CUSTOM) {
                $custom = $action;
            }
        }
        // If rule has conditions, or is a custom rule with a condition, return true.
        if ((!empty($rule['conditions']) && !$custom) ||
            ($custom && preg_match("/^ *(els)?if/i", $custom['sieve']))) {
            return true;
        }
        return false;
    }

   /**
    * Change the order of filter rules.
    *
    * @param integer $subject Array index of the rule to move
    * @param integer $target Array index to move rule to
    * @return boolean True if successful, false if not
    */
    function changeRuleOrder($subject, $target)
    {
        if ($target > (count($this->rules)-1)) {
            $target = count($this->rules)-1;
        }
        $newrules = array();
        if (isset($this->rules[$subject]) &&
            isset($this->rules[$target]) && $subject != $target) {
            for ($i=0; $i<count($this->rules); $i++) {
                if ($i == $subject) {
                    // Ignore.
                } else {
                    if ($i === $target) {
                        if ($subject < $target) { // Add after.
                            $newrules[] = $this->rules[$i];
                            $newrules[] = $this->rules[$subject];
                        } else {                  // Add before.
                            $newrules[] = $this->rules[$subject];
                            $newrules[] = $this->rules[$i];
                        }
                    } else {
                        $newrules[] = $this->rules[$i];
                    }
                }
            }
            $this->rules = $newrules;
        }
        return true;
    }

   /**
    * Add a new filter rule.
    *
    * @param array $rule The rule to add
    * @param integer $position Optional array position at which to add rule
    * @return integer Array index of added rule
    */
    function addRule($rule, $position=null)
    {
        if (!is_null($position) && is_int($position) &&
            $position < count($this->rules)) {
            $newrules = array();
            for ($i=0; $i<count($this->rules); $i++) {
                if ($i == $position) {
                    $newrules[] = $rule;
                }
                $newrules[] = $this->rules[$i];
            }
            $this->rules = $newrules;
            return $position;
        }
        return array_push($this->rules, $rule) - 1;
    }

   /**
    * Save an existing filter rule.
    *
    * @param array $rule The rule to save
    * @param integer $position Array index of rule
    * @return integer Array index of added rule
    */
    function saveRule($rule, $position)
    {
        if (!isset($this->rules[$position])) {
            return $this->addRule($rule);
        }
        $this->rules[$position] = $rule;
        return $position;
    }

   /**
    * Delete a filter rule.
    *
    * @param integer $rid Array index of the rule to delete
    * @return boolean True if successful, false if not
    */
    function deleteRule($rid)
    {
        if (isset($this->rules[$rid])) {
            unset($this->rules[$rid]);
            $this->rules = array_values($this->rules);
            return true;
        }
        return false;
    }

   /**
    * Get a filter rule.
    *
    * @param integer $rid Array index of the rule to delete
    * @return mixed Rule array if it exists, or null otherwise.
    */
    function getRule($id)
    {
        return (isset($this->rules[$id])) ? $this->rules[$id] : null;
    }

   /**
    * Get a special rule (vacation, whitelist etc).
    *
    * @param string $tag Special rule tag, one of RULE_TAG_* contants
    * @return mixed Array index if it exists, or null otherwise.
    */
    function getSpecialRuleId($tag)
    {
        for ($i=0; $i<count($this->rules); $i++) {
            if (!empty($this->rules[$i]['special']) && $this->rules[$i]['special'] == $tag) {
                return $i;
            }
        }
        return null;
    }

   /**
    * Get the script name.
    *
    * @return string The name of the script as it appears on the server
    */
    function getName()
    {
        return $this->name;
    }

   /**
    * Is filter rule enabled.
    *
    * @param integer $id The array index of rule to check
    * @return boolean True if rule if enabled, false if not
    */
    function isRuleEnabled($id)
    {
        if (isset($this->rules[$id]) &&
            $this->rules[$id]['status'] == 'ENABLED') {
            return true;
        }
        return false;
    }

}

?>
