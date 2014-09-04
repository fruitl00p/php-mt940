<?php
/**
 *
 */
class DescriptionTest_engine_mt940_banking_parser extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider statementProvider
	 *
	 * @param $input
	 * @param $expected
	 */
	public function testDebitCredit($input, $expected) {
		$engine = new \Kingsquare\Parser\Banking\Mt940\Engine\Unknown();
		$property = new ReflectionProperty($engine, 'currentTransactionData');
		$property->setAccessible(true);
		$property->setValue($engine, $input);

		$method = new ReflectionMethod($engine, 'parseTransactionDescription');
		$method->setAccessible(true);
		$this->assertEquals($expected, $method->invoke($engine));
	}

	/**
	 * @return array
	 */
	public function statementProvider() {
		return array(
				array(':86:This is a test', ''),
				array('
:86:This is a test', 'This is a test'),
				array('
:86:This is a test
', 'This is a test'),
				array('
:86:This is a test
:', 'This is a test:'),
				array('
:86:This is a test
:6', 'This is a test:6'),
				array('
:86:This is a test
:61', 'This is a test'),
				array('
:86:This is a test
:62', 'This is a test'),
				array('
:86:This is a test
: 62', 'This is a test: 62'),
				array('
:86:Spaarpot kantine', 'Spaarpot kantine'),
				array('
:86: ABN AMRO BANK>AMSTERDAM S1P468
22­07­2010 09:57 002 5595781
', 'ABN AMRO BANK>AMSTERDAM S1P46822­07­2010 09:57 002 5595781'),
		);
	}
}
