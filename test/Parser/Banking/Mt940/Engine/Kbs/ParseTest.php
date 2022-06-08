<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Kbs;

use Kingsquare\Parser\Banking\Mt940\Engine\Kbs;
use PHPUnit\Framework\TestCase;

class ParseTest extends TestCase
{
    /**
     * @var Kbs
     */
    private $engine;

    protected function setUp(): void
    {
        $this->engine = new Kbs();
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample'));
    }

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('KBS', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertCount(1, $statements);
        $first = $statements[0];

        $this->assertEquals('01-12-2020', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('01-12-2020', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals(-1870, $first->getDeltaPrice());

        $this->engine->loadString(file_get_contents(__DIR__.'/sample2'));
        
        $statements = $this->engine->parse();

        $this->assertCount(1, $statements);
        $first = $statements[0];

        $this->assertEquals('09-10-2020', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('09-10-2020', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals(-22755, $first->getDeltaPrice());
    }

    public function testParseTransactionEntryTimestamp()
    {
        $statements = $this->engine->parse();
        $transactions = reset($statements)->getTransactions();
        // the first has no entryTimestamp
        $firstTransaction = reset($transactions);
        $this->assertEquals('01-12-2020', $firstTransaction->getEntryTimestamp('d-m-Y'));

        // the last does have an entryTimestamp (custom edited)
        $lastTransaction = end($transactions);
        $this->assertEquals('01-12-2020', $lastTransaction->getEntryTimestamp('d-m-Y'));
        
        $this->engine->loadString(file_get_contents(__DIR__.'/sample2'));
        
        $statements = $this->engine->parse();
        $transactions = reset($statements)->getTransactions();
        // the first has no entryTimestamp
        $firstTransaction = reset($transactions);
        $this->assertEquals('09-10-2020', $firstTransaction->getEntryTimestamp('d-m-Y'));

        // the last does have an entryTimestamp (custom edited)
        $lastTransaction = end($transactions);
        $this->assertEquals('09-10-2020', $lastTransaction->getEntryTimestamp('d-m-Y'));
    }

    public function testParseTransactionDebitCredit()
    {
        $statements = $this->engine->parse();
        $transactions = reset($statements)->getTransactions();
        $firstTransaction = reset($transactions);

        $this->assertEquals('D', $firstTransaction->getDebitCredit());
    }
    
    public function testParseTransactionPrice()
    {
        $statements = $this->engine->parse();
        $transactions = reset($statements)->getTransactions();
        $firstTransaction = reset($transactions);

        $this->assertEquals(80, $firstTransaction->getPrice());
    }
}
