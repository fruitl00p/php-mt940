<?php
class ParseTest_rabo_mt940_banking_parser extends TestCase_test {
	var $engine = null;

	protected function setUp() {
		includeClass('banking_parser');
		includeClass('Mt940_banking_parser');
		includeClass('Engine_mt940_banking_parser');
		includeClass('Rabo_engine_mt940_banking_parser');
		includeClass('Statement_banking');
		includeClass('Transaction_banking');
		$this->engine = new Rabo_engine_mt940_banking_parser();
		$this->engine->loadString(file_get_contents(__DIR__ .'/sample'));
	}

	public function testParseStatementBank() {
		$this->assertEquals('Rabo', $this->engine->_parseStatementBank());
	}
}