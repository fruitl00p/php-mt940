<?php
/**
 *
 */
class ParseTest_mt940_banking_parser extends PHPUnit_Framework_TestCase {

	/**
	 *
	 */
	public function testParseReturnsArrayOnEmptySource() {
		$parser = new Mt940_banking_parser();
		$this->assertEquals(array(), $parser->parse(''));
	}
}