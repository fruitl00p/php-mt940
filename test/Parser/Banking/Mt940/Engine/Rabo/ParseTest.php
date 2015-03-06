<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Rabo;

use Kingsquare\Parser\Banking\Mt940\Engine\Rabo;

/**
 *
 */
class ParseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Rabo
     */
    private $engine = null;

    protected function setUp()
    {
        $this->engine = new Rabo;
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample'));
    }

    /**
     *
     */
    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('Rabo', $method->invoke($this->engine));
    }
}
