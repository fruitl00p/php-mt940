<?php
use \Kingsquare\Parser\Banking\Mt940\Engine\Ing;
/**
 *
 */
class ParseTest_ing_mt940_banking_parser extends PHPUnit_Framework_TestCase {
	/**
	 * @var Ing
	 */
	private $engine = null;

	protected function setUp() {
		$this->engine = new Ing;
		$this->engine->loadString(file_get_contents(__DIR__ .'/sample'));
	}

	/**
	 *
	 */
	public function testParseStatementBank() {
		$method = new ReflectionMethod($this->engine, 'parseStatementBank');
		$method->setAccessible(true);
		$this->assertEquals('ING', $method->invoke($this->engine));
	}
}
