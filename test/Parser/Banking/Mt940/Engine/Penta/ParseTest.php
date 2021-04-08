<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Penta;

use Kingsquare\Parser\Banking\Mt940\Engine\Penta;

/**
 */
class ParseTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Penta
     */
    private $engine;

    protected function setUp()
    {
        $this->engine = new Penta();
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample'));
    }

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('PENTA', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertCount(1, $statements);
        $first = $statements[0];

        $this->assertEquals('30-09-2020', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('30-09-2020', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals(10576.30, $first->getDeltaPrice());
    }

    public function testParseTransactionEntryTimestamp()
    {
        $statements = $this->engine->parse();
        $transactions = reset($statements)->getTransactions();
        // the first has no entryTimestamp
        $firstTransaction = reset($transactions);
        $this->assertEquals('02-09-2020', $firstTransaction->getEntryTimestamp('d-m-Y'));

        // the last does have an entryTimestamp (custom edited)
        $lastTransaction = end($transactions);
        $this->assertEquals('30-09-2020', $lastTransaction->getEntryTimestamp('d-m-Y'));
    }
}
