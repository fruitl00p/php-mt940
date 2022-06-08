<?php

namespace Kingsquare\Parser\Banking;

use PHPUnit\Framework\TestCase;

/**
 *
 */
class ParseTest extends TestCase
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
