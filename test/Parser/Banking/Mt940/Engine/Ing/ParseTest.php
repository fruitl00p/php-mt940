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
    private $engine;

    protected function setUp()
    {
        $this->engine = new Ing();
        $this->engine->loadString(file_get_contents(__DIR__.'/sample'));
    }

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('ING', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertCount(1, $statements);
        $first = $statements[0];

        $this->assertEquals('22-07-2010', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('23-07-2010', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals(-3.47, $first->getDeltaPrice());
    }

    /**
     *
     */
    public function testSanitizeDescription()
    {
        $statements = $this->engine->parse();
        $first = $statements[0]->getTransactions()[0];
        $this->assertEquals('', $first->getDescription());
    }

    /**
     *
     */
    public function testDescription()
    {
        $engine = new Ing();
        $engine->loadString(file_get_contents(__DIR__.'/sampleDescription'));
        $transactions = $engine->parse()[0]->getTransactions();
        $this->assertEquals('Direct Debet Descr TOTAAL        345 POSTEN', $transactions[0]->getDescription());
        $this->assertEquals('Direct Debet Descr TOTAAL 1 POST', $transactions[1]->getDescription());
    }
}
