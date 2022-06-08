<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Ing;

use Kingsquare\Parser\Banking\Mt940\Engine\Ing;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ParseTest extends TestCase
{
    /**
     * @var Ing
     */
    private $engine;

    protected function setUp(): void
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
    public function testParseTransactionEntryTimestamp()
    {
        $statements = $this->engine->parse();
        $transactions = reset($statements)->getTransactions();
        // the first has no entryTimestamp
        $firstTransaction = reset($transactions);
        $this->assertEquals(0, $firstTransaction->getEntryTimestamp());

        // the last does have an entryTimestamp (custom edited)
        $lastTransaction = end($transactions);
        $this->assertEquals('2010-07-21', $lastTransaction->getEntryTimestamp('Y-m-d'));
    }
    
    public function testParseTransactionDebitCredit()
    {
        $statements = $this->engine->parse();
        $transactions = reset($statements)->getTransactions();
        $firstTransaction = reset($transactions);

        $this->assertEquals('C', $firstTransaction->getDebitCredit());
    }
    
    public function testParseTransactionPrice()
    {
        $statements = $this->engine->parse();
        $transactions = reset($statements)->getTransactions();
        $firstTransaction = reset($transactions);

        $this->assertEquals(25.03, $firstTransaction->getPrice());
    }
}
