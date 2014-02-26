<?php

use \Kingsquare\Parser\Banking\Mt940\Engine\Abn;

/**
 *
 */
class ParseTest_abn_mt940_banking_parser extends PHPUnit_Framework_TestCase {
	/**
	 * @var Abn
	 */
	private $engine = null;

	protected function setUp() {
		$this->engine = new Abn;
		$this->engine->loadString(file_get_contents(__DIR__ .'/sample'));
	}

	/**
	 *
	 */
	public function testParseStatementBank() {
		$method = new ReflectionMethod($this->engine, '_parseStatementBank');
		$method->setAccessible(true);
		$this->assertEquals('ABN', $method->invoke($this->engine));
	}
}