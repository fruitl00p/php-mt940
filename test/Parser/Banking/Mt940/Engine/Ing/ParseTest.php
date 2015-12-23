<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Ing;

use Kingsquare\Parser\Banking\Mt940\Engine\Ing;

/**
 *
 */
class ParseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Ing
     */
    private $engine = null;

    protected function setUp()
    {
        $this->engine = new Ing;
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample'));
    }

    /**
     *
     */
    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('ING', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertEquals(1, count($statements));
        $first = $statements[0];

        $this->assertEquals('22-07-2010', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('23-07-2010', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals(-3.47, $first->getDeltaPrice());
    }

}
