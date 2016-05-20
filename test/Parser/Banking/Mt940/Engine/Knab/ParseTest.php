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
}
