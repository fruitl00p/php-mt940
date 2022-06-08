<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Kontist;

use Kingsquare\Parser\Banking\Mt940\Engine\Kontist;
use PHPUnit\Framework\TestCase;

/**
 */
class ParseTest extends TestCase
{

    /**
     *
     * @var Kontist
     */
    private $engine;

    protected function setUp(): void
    {
        $this->engine = new Kontist();
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample'));
    }

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('KONTIST', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertCount(1, $statements);
        $first = $statements[0];

        $this->assertEquals('31-01-2021', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('18-02-2021', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals(-395.76, $first->getDeltaPrice());
    }

    public function testParseTransactionEntryTimestamp()
    {
        $statements = $this->engine->parse();
        $transactions = reset($statements)->getTransactions();
        // the first has no entryTimestamp
        $firstTransaction = reset($transactions);
        $this->assertEquals('15-02-2021', $firstTransaction->getEntryTimestamp('d-m-Y'));

        // the last does have an entryTimestamp (custom edited)
        $lastTransaction = end($transactions);
        $this->assertEquals('17-02-2021', $lastTransaction->getEntryTimestamp('d-m-Y'));
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

        $this->assertEquals(50, $firstTransaction->getPrice());
    }
}
