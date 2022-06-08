<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Rabo;

use Kingsquare\Parser\Banking\Mt940\Engine\Rabo;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ParseTest extends TestCase
{
    /**
     * @var Rabo
     */
    private $engine;

    protected function setUp(): void
    {
        $this->engine = new Rabo();
        $this->engine->loadString(file_get_contents(__DIR__.'/sample'));
    }

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('Rabo', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertCount(39, $statements);
        $first = $statements[0];
        $last = end($statements);
        $this->assertEquals('06-01-2003', $first->getStartTimestamp('d-m-Y'));
        $this->assertEquals('07-01-2003', $first->getEndTimestamp('d-m-Y'));
        $this->assertEquals('08-01-2003', $last->getStartTimestamp('d-m-Y'));
        $this->assertEquals('09-01-2003', $last->getEndTimestamp('d-m-Y'));
    }

    public function testInitialNegativeStatementBalance()
    {
        $this->engine->loadString(file_get_contents(__DIR__.'/sample2'));
        $statements = $this->engine->parse();
        $this->assertEquals(-1000.12, $statements[0]->getStartPrice());
    }

    public function testCorrectHandlingOfVariousStatementPricing()
    {
        $this->engine->loadString(file_get_contents(__DIR__.'/sample2'));
        $statements = $this->engine->parse();
        $this->assertEquals(-1000.12, $statements[0]->getStartPrice());
        $this->assertEquals(2145.23, $statements[0]->getEndPrice());
        $this->assertEquals(-3145.35, $statements[0]->getDeltaPrice());
    }
    
    public function testHandlingOfDescriptions() {
        $this->engine->loadString(file_get_contents(__DIR__.'/sample'));
        $statements = $this->engine->parse();
        $this->assertSame('Contante storting Overige', $statements[4]->getTransactions()[1]->getDescription());
        $this->assertSame('INVOICE 38', $statements[14]->getTransactions()[3]->getDescription());
        $this->assertSame('VOLGENS AFSPRAAK', $statements[15]->getTransactions()[0]->getDescription());
        $this->assertSame('FAKTUUR 3549', $statements[15]->getTransactions()[1]->getDescription());

        $this->engine->loadString(file_get_contents(__DIR__.'/sample3'));
        $statements = $this->engine->parse();
        // enclosed
        $this->assertSame('674725433 1120000153447185 14144467636004962', $statements[0]->getTransactions()[0]->getDescription());
        // ending
        $this->assertSame('861835-574631143', $statements[0]->getTransactions()[2]->getDescription());
        // with slash
        $this->assertSame('/IBS.00008908/ 1001680-P796142 KINDEROPVANG', $statements[0]->getTransactions()[3]->getDescription());

        $this->assertSame('Factuur 307472', $statements[1]->getTransactions()[0]->getDescription());

        $this->engine->loadString(<<<PURPTEST
:940:
:20:940S130403
:25:NL50RABO0123456789
:28C:0
:60F:C130402EUR000000001147,95
:20:940S160503
:25:NL93RABO0157787990 EUR
:28C:16085
:60F:C160502EUR000000146645,88
:61:160503D000000000015,55N102EREF
NL34DEUT0499438906
:86:/EREF/02-06-2016 09:00 1230000456789011/BENM//NAME/Some Name
d company/REMI/some descripton here that
ends with/PURP//CD/EPAY
:62F:C160503EUR000000146630,33
PURPTEST
);
        $statements = $this->engine->parse();
        $this->assertSame('some descripton here thatends with', $statements[1]->getTransactions()[0]->getDescription());
    }

    public function testHandlingOfEREF() {
        $this->engine->loadString(file_get_contents(__DIR__.'/sample4'));
        $statements = $this->engine->parse();
        $this->assertSame('20151208123123987 0030001100999991 Rabobank.nl - Order 347347', $statements[0]->getTransactions()[0]->getDescription());
    }

    public function testHandlingOfPREF() {
        $this->engine->loadString(file_get_contents(__DIR__.'/sample4'));
        $statements = $this->engine->parse();
        $this->assertSame('PmtInfId-20151208-987', $statements[0]->getTransactions()[1]->getDescription());
    }
    
    public function testParseTransactionDebitCredit()
    {
        $statements = $this->engine->parse();
        $transactions = $statements[5]->getTransactions();
        $firstTransaction = reset($transactions);

        $this->assertEquals('C', $firstTransaction->getDebitCredit());
    }
    
    public function testParseTransactionPrice()
    {
        $statements = $this->engine->parse();
        $transactions = $statements[5]->getTransactions();
        $firstTransaction = reset($transactions);

        $this->assertEquals(500, $firstTransaction->getPrice());
    }
}
