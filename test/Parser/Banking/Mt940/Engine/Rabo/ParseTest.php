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

    public function testParsesAllFoundStatements() {
        $statements = $this->engine->parse();
        $this->assertEquals(39, count($statements));
        $this->assertEquals('06-01-2003', reset($statements)->getTimestamp('d-m-Y'));
        $this->assertEquals('08-01-2003', end($statements)->getTimestamp('d-m-Y'));
    }

    public function testInitialNegativeStatementBalance() {
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample2'));
        $statements = $this->engine->parse();
        $this->assertEquals(-1000.12, $statements[0]->getStartPrice());
    }
}
