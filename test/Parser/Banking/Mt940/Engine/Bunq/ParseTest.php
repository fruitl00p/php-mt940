<?php
namespace Kingsquare\Parser\Banking\Mt940\Engine\Bunq;

use Kingsquare\Parser\Banking\Mt940\Engine\Bunq;

/**
 */
class ParseTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Bunq
     */
    private $engine;

    protected function setUp()
    {
        $this->engine = new Bunq();
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample'));
    }

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('BUNQ', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertCount(1, $statements);
        $first = $statements[0];

        $this->assertEquals('31-01-2019', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('31-05-2019', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals(26.67, $first->getDeltaPrice());
    }

    public function testParseTransactionEntryTimestamp()
    {
        $statements = $this->engine->parse();
        $transactions = reset($statements)->getTransactions();
        // the first has no entryTimestamp
        $firstTransaction = reset($transactions);
        $this->assertEquals('31-01-2019', $firstTransaction->getEntryTimestamp('d-m-Y'));

        // the last does have an entryTimestamp (custom edited)
        $lastTransaction = end($transactions);
        $this->assertEquals('24-05-2019', $lastTransaction->getEntryTimestamp('d-m-Y'));
    }
}
