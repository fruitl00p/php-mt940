<?php
/**
 *
 */
class DebitCreditTest_engine_mt940_banking_parser extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider statementProvider
	 * @param string $dOrC D|C
	 * @param string $statement
	 */
	public function testDebitCredit($dOrC, $statement) {
		$engine = new Unknown_engine_mt940_banking_parser();
		$property = new ReflectionProperty($engine, '_currentTransactionData');
		$property->setAccessible(true);

		$method = new ReflectionMethod($engine, '_parseTransactionDebitCredit');
		$method->setAccessible(true);

		$property->setValue($engine, $statement);
		$this->assertEquals($dOrC, $method->invoke($engine));
	}

	/**
	 * @return array
	 */
	public function statementProvider() {
		return array(
			array('D', ':61:030111D000000000500.00NMSC1173113681      ROBECO'),
			array('C', ':61:100628C49,37NOV NONREF'),
			array('D', ':61:100628D49,37this is a Testds'),
			array('C', ':61:100628C49,37D is actually a'),
			array('C', ':61:100628C36,07NVZ NONREF'),
			array('C', ':61:1004080408C23,7N196NONREF'),
			array('D', ':61:030109D000000000110.00NMSC644530030       INSTANT-LOTERY, STG.NAT'),
			array('D', ':61:1004160416D1133,57N422NONREF'),
		);
	}
}
