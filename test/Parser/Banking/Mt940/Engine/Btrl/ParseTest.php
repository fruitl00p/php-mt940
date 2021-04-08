<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine\Btrl;

use Kingsquare\Parser\Banking\Mt940\Engine\Btrl;

/**
 *
 */
class ParseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Btrl
     */
    private $engine;

    protected function setUp(): void
    {
        $this->engine = new Btrl();
        $this->engine->loadString(file_get_contents(__DIR__.'/sample'));
    }

    public function testParseStatementBank():void
    {
        $method = new \ReflectionMethod($this->engine, 'parseStatementBank');
        $method->setAccessible(true);
        $this->assertEquals('BTRL', $method->invoke($this->engine));
    }

	public function testParseTransactionPrice(): void 
	{
		$statements = $this->engine->parse();
		$this->assertCount(1, $statements);
        $transactions = $statements[0]->getTransactions();
		
		$this->assertEquals('980.42', $transactions[0]->getPrice());
	}
	
}
