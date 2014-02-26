<?php

/**
 * @package Kingsquare\Parser\Banking
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Mt940_banking_parser extends Banking_parser {
	/* @var Engine_mt940_banking_parser engine */
	protected $engine;

	/**
	 * Parse the given string into an array of statement_banking objects
	 * @param string $string
	 * @return array
	 */
	function parse($string) {
		if (!empty($string)) {
			// load engine
			$this->engine = Engine_mt940_banking_parser::__getInstance($string);
			if ($this->engine instanceof Engine_mt940_banking_parser) {
				// parse using the engine
				return $this->engine->parse();
			}
		}
		return array();
	}
}