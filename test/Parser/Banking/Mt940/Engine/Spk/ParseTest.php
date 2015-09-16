<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Spk;

use Kingsquare\Parser\Banking\Mt940\Engine\Spk;

/**
 *
 */
class ParseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Spk
     */
    private $engine = null;

    protected function setUp()
    {
        $this->engine = new Spk;
        $this->engine->loadString(file_get_contents(__DIR__ . '/sample'));
    }

    /**
     *
     */
    public function testParseStatementBank()
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('Spk', $method->invoke($this->engine));
    }

    /**
     *
     */
    public function testHasTheRightAmountOfTransactions()
    {
        $statements = $this->engine->parse();
        $this->assertSame(4, count($statements));
        $tranactions = [];
        foreach ($statements as $statement) {
            $tranactions = array_merge($tranactions, $statement->getTransactions());
        }
        $this->assertSame(10, count($tranactions));
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
}
