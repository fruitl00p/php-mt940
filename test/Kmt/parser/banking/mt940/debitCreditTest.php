<?php
/**
 *
 */
class DebitCreditTest_engine_mt940_banking_parser extends PHPUnit_Framework_TestCase {
	/**
	 * @var Engine_mt940_banking_parser
	 */
	private $engine = null;

	protected function setUp() {
		$this->engine = new Unknown_engine_mt940_banking_parser();
	}

	/**
	 * @dataProvider statementProvider
	 * @param string $dOrC D|C
	 * @param string $statement
	 */
	public function testDebitCredit($dOrC, $statement) {
		$this->engine->_currentTransactionData = $statement;
		$this->assertEquals($dOrC, $this->engine->_parseTransactionDebitCredit());
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
