<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Abn;

use Kingsquare\Parser\Banking\Mt940\Engine\Abn;

/**
 *
 */
class ParseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Abn
     */
    private $engine = null;

    protected function setUp()
    {
        $this->engine = new Abn();
        $this->engine->loadString(file_get_contents(__DIR__.'/sample'));
    }

    /**
     *
     */
    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('ABN', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertEquals(4, count($statements));
        $first = $statements[0];
        $last = end($statements);

        $this->assertEquals('23-06-2009', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('24-06-2009', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals(210.5, $first->getDeltaPrice());

        $this->assertEquals('23-06-2009', $last->getStartTimestamp('d-m-Y'));
        $this->assertEquals('24-06-2009', $last->getEndTimestamp('d-m-Y'));
    }
}
