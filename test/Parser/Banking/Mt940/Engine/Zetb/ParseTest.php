<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Zetb;

use Kingsquare\Parser\Banking\Mt940\Engine\Zetb;
use PHPUnit\Framework\TestCase;

class ParseTest extends TestCase
{
    /**
     * @var Zetb
     */
    private $engine;

    protected function setUp(): void
    {
        $this->engine = new Zetb();
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample'));
    }

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('ZETB', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertCount(1, $statements);
        $first = $statements[0];

        $this->assertEquals('18-12-2020', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('30-11-2020', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals(3743.91, $first->getDeltaPrice());
    }

    public function testParseTransactionEntryTimestamp()
    {
        $statements = $this->engine->parse();
        $transactions = reset($statements)->getTransactions();
        // the first has no entryTimestamp
        $firstTransaction = reset($transactions);
        $this->assertEquals('30-11-2020', $firstTransaction->getEntryTimestamp('d-m-Y'));

        // the last does have an entryTimestamp (custom edited)
        $lastTransaction = end($transactions);
        $this->assertEquals('30-11-2020', $lastTransaction->getEntryTimestamp('d-m-Y'));
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

        $this->assertEquals(220000, $firstTransaction->getPrice());
    }
}
