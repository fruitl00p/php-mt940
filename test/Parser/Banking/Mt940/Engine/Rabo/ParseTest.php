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

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('Rabo', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertEquals(39, count($statements));
        $first = $statements[0];
        $last = end($statements);
        $this->assertEquals('06-01-2003', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('07-01-2003', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals('08-01-2003', $last->getStartTimestamp('d-m-Y'));
        $this->assertEquals('09-01-2003', $last->getEndTimestamp('d-m-Y'));
    }

    public function testInitialNegativeStatementBalance()
    {
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample2'));
        $statements = $this->engine->parse();
        $this->assertEquals(-1000.12, $statements[0]->getStartPrice());
    }

    public function testCorrectHandlingOfVariousStatementPricing()
    {
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample2'));
        $statements = $this->engine->parse();
        $this->assertEquals(-1000.12, $statements[0]->getStartPrice());
        $this->assertEquals(2145.23, $statements[0]->getEndPrice());
        $this->assertEquals(-3145.35, $statements[0]->getDeltaPrice());
    }
}
