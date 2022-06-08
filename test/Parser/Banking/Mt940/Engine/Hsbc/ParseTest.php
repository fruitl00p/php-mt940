<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Hsbc;

use Kingsquare\Parser\Banking\Mt940\Engine\Hsbc;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ParseTest extends TestCase
{
    /**
     * @var Hsbc
     */
    private $engine;

    protected function setUp(): void
    {
        $this->engine = new Hsbc();
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample'));
    }

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('HSBC', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertCount(1, $statements);
        $first = $statements[0];
        $last = end($statements);
        $this->assertEquals('2012-03-29', $first->getStartTimestamp('Y-m-d'));
        $this->assertEquals('2012-03-29', $first->getEndTimestamp('Y-m-d'));
        $this->assertEquals('2012-03-29', $last->getStartTimestamp('Y-m-d'));
        $this->assertEquals('2012-03-29', $last->getEndTimestamp('Y-m-d'));
    }

    public function testStatementBalance()
    {
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample'));
        $statements = $this->engine->parse();
        $this->assertEquals(0.00, $statements[0]->getStartPrice());
        $this->assertEquals(0.00, $statements[0]->getEndPrice());
        $this->assertEquals(0.00, $statements[0]->getDeltaPrice());
        $this->assertEquals('HKD', $statements[0]->getCurrency());
        $this->assertEquals('D', $statements[0]->getTransactions()[0]->getDebitCredit());
        $this->assertEquals(20000, $statements[0]->getTransactions()[0]->getPrice());
        $this->assertEquals('1004688128', $statements[0]->getTransactions()[0]->getAccount());
        $this->assertEquals('//6128522200250001', $statements[0]->getTransactions()[0]->getAccountName());
    }

    public function testHandlingOfDescriptions()
    {
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample'));
        $statements = $this->engine->parse();
        $first = $statements[0];
        $expected = '/OCMT/USD2853,12/PAYMENT OF INVOICES 204011665 20401 1687/OGB/WXXXXX BANKING CORPORATION /VA/123456XXXXXXBENEFICIARY INFO/OSDR/HSBCHKHH-';
        $this->assertSame($expected, $first->getTransactions()[0]->getDescription());

        $expected = '123456XXXXXX';
        $this->assertSame($expected, $first->getTransactions()[0]->getVirtualAccount());

        $this->engine->loadString(<<<EOF
:20:AIU0006362100017
:25:808XXXXXX292
:28C:00001/00001
:60F:C120329HKD0,00
:61:1203290329DD20000,00NTRF1004688128      //6128522200250001
:86:/A
/B
/C
:62F:C120329HKD0,00
:64:C120329HKD0,00
:86:/D
-
EOF
        );
        $statements = $this->engine->parse();
        $this->assertSame('/A/B/C/D-', $statements[0]->getTransactions()[0]->getDescription());
    }
}
