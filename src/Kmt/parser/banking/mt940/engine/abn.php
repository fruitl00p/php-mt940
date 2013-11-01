<?php

/**
 *
 * @package Kmt\Parser\Banking\Mt940\Engine
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 * @license http://opensource.org/licenses/gpl-2.0.php  Open Software License (GPLv2)
 */
class Abn_engine_mt940_banking_parser extends Engine_mt940_banking_parser {
	/**
	 * returns the name of the bank
	 * @return string
	 */
	function _parseStatementBank() {
		return 'ABN';
	}

	/**
	 * Overloaded: ABN Amro shows the GIRO
	 * includes fix for 'for GIRO 1234567 TEST 201009063689 CLIEOP 21-9' and translates that into 1234567
	 * @return string
	 * @see Engine_mt940_banking_parser::_sanitizeAccount
	 */
	function _parseTransactionAccount() {
		$results = parent::_parseTransactionAccount();
		if (empty($results)) {
			$giroMatch = $ibanMatch = array();
			if (preg_match('/^:86:GIRO(.{9})/im', $this->getCurrentTransactionData(), $giroMatch) && !empty($giroMatch[1])) {
				$results = $giroMatch[1];
			}

			if (preg_match('!^:86:/.*/IBAN/(.*?)/!m', $this->getCurrentTransactionData(), $ibanMatch) && !empty($ibanMatch[1])) {
				$results = $ibanMatch[1];
			}
		}
		return $this->_sanitizeAccount($results);
	}

	/**
	 * Overloaded: ABN Amro shows the GIRO and fixes newlines etc
	 * @return string
	 * @see Engine_mt940_banking_parser::_sanitizeAccountName
	 */
	function _parseTransactionAccountName() {
		$results = parent::_parseTransactionAccountName();
		if ($results !== '') {
			return $results;
		}

		if (preg_match('/:86:(GIRO|BGC\.)\s+[\d]+ (.+)/', $this->getCurrentTransactionData(), $results)
				&& !empty($results[2])) {
			return $this->_sanitizeAccountName($results[2]);
		}

		if (preg_match('/:86:.+\n(.*)\n/', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->_sanitizeAccountName($results[1]);
		}
		return '';
	}

	/**
	 * Overloaded: ABNAMRO uses the :61: date-part of the field for two values:
	 * Valuetimestamp (YYMMDD) and Entry date (book date) (MMDD)
	 *
	 * @return int
	 */
	function _parseTransactionEntryTimestamp() {
		$results = array();
		if (preg_match('/^:61:\d{6}(\d{4})[C|D]/', $this->getCurrentTransactionData(), $results) && !empty($results[1])) {
			return $this->_sanitizeTimestamp($results[1], 'md');
		}
		return 0;
	}
}