<?php

namespace Kingsquare\Parser\Banking;

/**
 *
 */
class ParseTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testParseReturnsArrayOnEmptySource()
    {
        $parser = new Mt940();
        $this->assertEquals([], $parser->parse(''));
    }
}
