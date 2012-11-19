<?php

/**
 *
 * @package Kmt\Parser\Banking
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) 2004 - 2012 Kingsquare BV (http://www.kingsquare.nl)
 * @license http://opensource.org/licenses/gpl-2.0.php  Open Software License (GPLv2)
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