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
}
