<?php
/**
 *
 */
class ParseTest_rabo_mt940_banking_parser extends PHPUnit_Framework_TestCase {
	/**
	 * @var Rabo_engine_mt940_banking_parser
	 */
	private $engine = null;

	protected function setUp() {
		$this->engine = new Rabo_engine_mt940_banking_parser();
		$this->engine->loadString(file_get_contents(__DIR__ .'/sample'));
	}

	/**
	 *
	 */
	public function testParseStatementBank() {
		$this->assertEquals('Rabo', $this->engine->_parseStatementBank());
	}
}