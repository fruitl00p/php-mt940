<?php
/**
 *
 */
class ParseTest_ing_mt940_banking_parser extends PHPUnit_Framework_TestCase {
	/**
	 * @var Ing_engine_mt940_banking_parser
	 */
	private $engine = null;

	protected function setUp() {
		$this->engine = new Ing_engine_mt940_banking_parser();
		$this->engine->loadString(file_get_contents(__DIR__ .'/sample'));
	}

	/**
	 *
	 */
	public function testParseStatementBank() {
		$method = new ReflectionMethod($this->engine, '_parseStatementBank');
		$method->setAccessible(true);
		$this->assertEquals('ING', $method->invoke($this->engine));
	}
}