<?php
class ParseTest_unknown_mt940_banking_parser extends TestCase_test {
	var $engine = null;

	protected function setUp() {
		includeClass('banking_parser');
		includeClass('Mt940_banking_parser');
		includeClass('Engine_mt940_banking_parser');
		includeClass('Unknown_engine_mt940_banking_parser');
		$this->engine = new Unknown_engine_mt940_banking_parser();
	}

	public function testParse() {
		$this->markTestIncomplete('The unkonwn / default is still empty and reverts to engine');
	}
}