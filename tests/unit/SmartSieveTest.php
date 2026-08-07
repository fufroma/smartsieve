<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../lib/SmartSieve.lib';

class SmartSieveTest extends TestCase
{
    public function testGetGETReturnsValueOrDefault(): void
    {
        $_GET['present'] = 'value';
        $this->assertSame('value', SmartSieve::getGET('present'));
        $this->assertSame('def', SmartSieve::getGET('absent', 'def'));
        $this->assertNull(SmartSieve::getGET('absent'));
    }

    public function testGetPOSTReturnsValueOrDefault(): void
    {
        $_POST['present'] = 'value';
        $this->assertSame('value', SmartSieve::getPOST('present'));
        $this->assertSame('def', SmartSieve::getPOST('absent', 'def'));
    }

    public function testGetFormValuePrefersPost(): void
    {
        $_GET['both'] = 'from-get';
        $_POST['both'] = 'from-post';
        $this->assertSame('from-post', SmartSieve::getFormValue('both'));
    }

    public function testRemoveMagicQuotesIsPassThrough(): void
    {
        $this->assertSame('a"b\'c', SmartSieve::removeMagicQuotes('a"b\'c'));
        $this->assertSame(array('x' => 'a"b'), SmartSieve::removeMagicQuotes(array('x' => 'a"b')));
    }
}
