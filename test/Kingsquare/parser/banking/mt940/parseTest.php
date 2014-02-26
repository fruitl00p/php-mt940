<?php

/**
 *
 */
class ParseTest_mt940_banking_parser extends PHPUnit_Framework_TestCase {

	/**
	 *
	 */
	public function testParseReturnsArrayOnEmptySource() {
		$parser = new \Kingsquare\Parser\Banking\Mt940();
		$this->assertEquals(array(), $parser->parse(''));
	}
}