<?php
class ParseTest_mt940_banking_parser extends TestCase_test {

	protected function setUp() {
		includeClass('banking_parser');
		includeClass('Mt940_banking_parser');
	}
	
	public function testParseReturnsArrayOnEmptySource() {
		$parser = new Mt940_banking_parser();
		$this->assertEquals(array(), $parser->parse(''));
	}
}