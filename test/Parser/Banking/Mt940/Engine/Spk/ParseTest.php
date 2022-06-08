<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Spk;

use Kingsquare\Banking\Statement;
use Kingsquare\Parser\Banking\Mt940\Engine\Spk;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ParseTest extends TestCase
{
    /**
     * @var Spk
     */
    private $engine;

    protected function setUp(): void
    {
        $this->engine = new Spk();
        $this->engine->loadString(file_get_contents(__DIR__.'/sample'));
    }

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('Spk', $method->invoke($this->engine));
    }

    public function testHasTheRightAmountOfTransactions()
    {
        $statements = $this->engine->parse();
        $this->assertCount(4, $statements);

        $transactions = array_map(static function(Statement $statement) {
            return $statement->getTransactions();
        }, $statements);
        $tranactions = call_user_func_array('array_merge', $transactions);

        $this->assertCount(10, $tranactions);
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();
        $first = $statements[0];
        $last = end($statements);
        $this->assertEquals('17-02-2010', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('17-02-2010', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals('18-02-2010', $last->getStartTimestamp('d-m-Y'));
        $this->assertEquals('18-02-2010', $last->getEndTimestamp('d-m-Y'));
    }

    public function testParseTransactionDebitCredit()
    {
        $statements = $this->engine->parse();
        $transactions = reset($statements)->getTransactions();
        $firstTransaction = reset($transactions);

        $this->assertEquals('C', $firstTransaction->getDebitCredit());
    }
}
