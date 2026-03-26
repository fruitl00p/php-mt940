<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Fnom;

use Kingsquare\Parser\Banking\Mt940\Engine\Fnom;

/**
 *
 */
class ParseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Fnom
     */
    private $engine;

    protected function setUp()
    {
        $this->engine = new Fnom();
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample'));
    }

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('FINOM', $method->invoke($this->engine));
    }

}
