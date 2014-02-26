<?php

/**
 * @package Kingsquare\Parser\Banking\Mt940\Engine
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Ing_engine_mt940_banking_parser extends Engine_mt940_banking_parser {
	/**
	 * returns the name of the bank
	 * @return string
	 */
	protected function _parseStatementBank() {
		return 'ING';
	}
}