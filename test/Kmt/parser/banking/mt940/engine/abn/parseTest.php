<?php
/**
 *
 */
class ParseTest_abn_mt940_banking_parser extends PHPUnit_Framework_TestCase {
	/**
	 * @var Abn_engine_mt940_banking_parser
	 */
	private $engine = null;

	protected function setUp() {
		$this->engine = new Abn_engine_mt940_banking_parser();
		$this->engine->loadString(file_get_contents(__DIR__ .'/sample'));
	}

	/**
	 *
	 */
	public function testParseStatementBank() {
		$this->assertEquals('ABN', $this->engine->_parseStatementBank());
	}
}