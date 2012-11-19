<?php
/**
 *
 */
class ParseTest_unknown_mt940_banking_parser extends PHPUnit_Framework_TestCase {
	/**
	 * @var Unknown_engine_mt940_banking_parser
	 */
	private $engine = null;

	protected function setUp() {
		$this->engine = new Unknown_engine_mt940_banking_parser();
	}

	/**
	 *
	 */
	public function testParse() {
		$this->markTestIncomplete('The unkonwn / default is still empty and reverts to engine');
	}
}