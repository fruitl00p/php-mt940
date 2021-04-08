<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Asn;

use Kingsquare\Parser\Banking\Mt940\Engine\Asn;

class ParseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Asn
     */
    private $engine;

    protected function setUp()
    {
        $this->engine = new Asn();
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample'));
    }

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('ASN', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertCount(7, $statements);
        $first = $statements[0];

        $this->assertEquals('02-01-2020', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('02-01-2020', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals(2000, $first->getDeltaPrice());
    }

    public function testParseTransactionEntryTimestamp()
    {
        $statements = $this->engine->parse();
        $transactions = reset($statements)->getTransactions();
        // the first has no entryTimestamp
        $firstTransaction = reset($transactions);
        $this->assertEquals('02-01-2020', $firstTransaction->getEntryTimestamp('d-m-Y'));

        // the last does have an entryTimestamp (custom edited)
        $lastTransaction = end($transactions);
        $this->assertEquals('02-01-2020', $lastTransaction->getEntryTimestamp('d-m-Y'));
    }
}
