<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Abn;

use Kingsquare\Parser\Banking\Mt940\Engine\Abn;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ParseTest extends TestCase
{
    /**
     * @var Abn
     */
    private $engine;

    protected function setUp(): void
    {
        $this->engine = new Abn();
        $this->engine->loadString(file_get_contents(__DIR__.'/sample'));
    }

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('ABN', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertCount(4, $statements);
        $first = $statements[0];
        $last = end($statements);

        $this->assertEquals('23-06-2009', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('24-06-2009', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals(210.5, $first->getDeltaPrice());

        $this->assertEquals('23-06-2009', $last->getStartTimestamp('d-m-Y'));
        $this->assertEquals('24-06-2009', $last->getEndTimestamp('d-m-Y'));
    }

    public function testHandleEntryYearRollover()
    {
        $this->engine->loadString(file_get_contents(__DIR__.'/sample2'));
        $statements = $this->engine->parse();

        $this->assertCount(1, $statements);
        list($sameday, $nextDay, $nextMonth, $nextYear) = $statements[0]->getTransactions();

        $this->assertEquals('01-01-2009', $sameday->getValueTimestamp('d-m-Y'));
        $this->assertEquals('01-01-2009', $sameday->getEntryTimestamp('d-m-Y'));

        $this->assertEquals('01-01-2009', $nextDay->getValueTimestamp('d-m-Y'));
        $this->assertEquals('02-01-2009', $nextDay->getEntryTimestamp('d-m-Y'));

        $this->assertEquals('01-01-2009', $nextMonth->getValueTimestamp('d-m-Y'));
        $this->assertEquals('01-02-2009', $nextMonth->getEntryTimestamp('d-m-Y'));

        $this->assertEquals('31-12-2009', $nextYear->getValueTimestamp('d-m-Y'));
        $this->assertEquals('01-01-2010', $nextYear->getEntryTimestamp('d-m-Y'));
    }

    public function testIssue48()
    {
        $this->engine->loadString(file_get_contents(__DIR__.'/issue48'));
        $statements = $this->engine->parse();

        $this->assertCount(1, $statements);
        $transactions = $statements[0]->getTransactions();

        $this->assertEquals('15-12-2016', $transactions[0]->getValueTimestamp('d-m-Y'));
        $this->assertEquals('15-12-2016', $transactions[0]->getEntryTimestamp('d-m-Y'));

        $this->assertEquals('15-12-2016', $transactions[1]->getValueTimestamp('d-m-Y'));
        $this->assertEquals('15-12-2016', $transactions[1]->getEntryTimestamp('d-m-Y'));
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

        $this->assertEquals(7.5, $firstTransaction->getPrice());
    }
}
