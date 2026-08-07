<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../lib/Script.php';

class ScriptTest extends TestCase
{
    public function testHasConditionTrueWhenConditionsPresent(): void
    {
        $rule = array(
            'actions' => array(array('type' => ACTION_FILEINTO)),
            'conditions' => array(array('type' => TEST_HEADER)),
        );
        $this->assertTrue(Script::hasCondition($rule));
    }

    public function testHasConditionFalseWhenNoConditions(): void
    {
        $rule = array('actions' => array(), 'conditions' => array());
        $this->assertFalse(Script::hasCondition($rule));
    }

    public function testHasConditionWithCustomIfRule(): void
    {
        $rule = array(
            'actions' => array(array('type' => ACTION_CUSTOM, 'sieve' => 'if header :contains "X" "y" { keep; }')),
            'conditions' => array(),
        );
        $this->assertTrue(Script::hasCondition($rule));
    }

    public function testEscapeUnescapeCharsRoundTrip(): void
    {
        $script = new Script('test');
        $input = "line1\r\nline2 & more \\ end";
        $this->assertSame($input, $script->unescapeChars($script->escapeChars($input)));
    }
}
