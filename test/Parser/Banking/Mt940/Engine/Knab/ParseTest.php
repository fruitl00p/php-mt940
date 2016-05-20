<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Knab;

use Kingsquare\Parser\Banking\Mt940\Engine\Knab;

/**
 *
 */
class ParseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Knab
     */
    private $engine = null;

    protected function setUp()
    {
        $this->engine = new Knab();
        $this->engine->loadString(file_get_contents(__DIR__.'/sample'));
    }

    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('KNAB', $method->invoke($this->engine));
    }

    public function testParsesAllFoundStatements()
    {
        $statements = $this->engine->parse();

        $this->assertEquals(1, count($statements));
        $this->assertEquals('03-12-2015', $statements[0]->getStartTimestamp('d-m-Y'));
        $this->assertEquals('03-12-2015', $statements[0]->getStartTimestamp('d-m-Y'));
    }

    public function testCorrectHandlingOfVariousStatementPricing()
    {
        $statements = $this->engine->parse();
        $this->assertEquals(1000.21, $statements[0]->getStartPrice());
        $this->assertEquals(945.21, $statements[0]->getEndPrice());
        $this->assertEquals(55, $statements[0]->getDeltaPrice());
    }

    public function testUnStructuredMt940() {
        $statements = $this->engine->parse();
        $structuredTransaction = $statements[0]->getTransactions()[1];
        $this->assertEquals('NL52RABO0326203011', $structuredTransaction->getAccount());
        $this->assertEquals('SOME NAME', $structuredTransaction->getAccountName());
        $this->assertEquals('61385002542767281000000000000000000 6676341986995664 TEST PURCHAS567890', $structuredTransaction->getDescription());

        $structuredTransaction = $statements[0]->getTransactions()[3];
        $this->assertEquals('', $structuredTransaction->getAccount());
        $this->assertEquals('POS', $structuredTransaction->getAccountName());
        $this->assertEquals('03-12-2015 16:04 PAS: 1122', $structuredTransaction->getDescription());
    }

    public function testStructuredMt940() {
        $statements = $this->engine->parse();
        $structuredTransaction = $statements[0]->getTransactions()[4];
        $this->assertEquals('437015300', $structuredTransaction->getAccount());
        $this->assertEquals('ACHMEA SCHADEVERZEKERINGEN N.V.', $structuredTransaction->getAccountName());
        $this->assertEquals('EERSTE MAAND, MAART VERZEKERING 5002100023 310160', $structuredTransaction->getDescription());
    }
}
