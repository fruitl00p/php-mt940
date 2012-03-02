<?php
class GetInstanceTest_engine_mt940_banking_parser extends TestCase_test {

	protected function setUp() {
		includeClass('banking_parser');
		includeClass('Mt940_banking_parser');
		includeClass('Engine_mt940_banking_parser');
	}

	public function testUnknownEngineRaisesANotice() {
		$error_reporting = error_reporting();
		error_reporting(E_ALL);
		try {
			$engine = Engine_mt940_banking_parser::__getInstance('this is an unknown format :)');
		}
		catch(PHPUnit_Framework_Error $exptected) {
			error_reporting($error_reporting);
			$this->assertInstanceOf('PHPUnit_Framework_Error', $exptected);
			return;
		}
		error_reporting($error_reporting);
		$this->fail('Did not receive the notice');
	}

	public function testUnknownEngine() {
		$engine = @Engine_mt940_banking_parser::__getInstance('this is an unknown format :)');
		$this->assertInstanceOf('Unknown_engine_mt940_banking_parser', $engine);
	}

	public function testAbnEngine() {
		$sample = file_get_contents(__DIR__ .'/abn/sample');
		$engine = Engine_mt940_banking_parser::__getInstance($sample);

		$this->assertInstanceOf('Abn_engine_mt940_banking_parser', $engine);
	}

	public function testIngEngine() {
		$sample = file_get_contents(__DIR__ .'/ing/sample');
		$engine = Engine_mt940_banking_parser::__getInstance($sample);

		$this->assertInstanceOf('Ing_engine_mt940_banking_parser', $engine);
	}

	public function testRaboEngine() {
		$sample = file_get_contents(__DIR__ .'/rabo/sample');
		$engine = Engine_mt940_banking_parser::__getInstance($sample);

		$this->assertInstanceOf('Rabo_engine_mt940_banking_parser', $engine);
	}
}