<?php
namespace Kingsquare\Parser\Banking\Mt940\Engine;
use Kingsquare\Parser\Banking\Mt940\Engine;

/**
 * @package Kingsquare\Parser\Banking\Mt940\Engine
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Rabo extends Engine {
	/**
	 * returns the name of the bank
	 * @return string
	 */
	protected function _parseStatementBank() {
		return 'Rabo';
	}

	/**
	 * Overloaded: Rabo has different way of storing account info
	 * @inheritdoc
	 */
	protected function _parseTransactionAccount() {
		$results = array();
		if (preg_match('/^:61:.{26}(.{16})/im', $this->getCurrentTransactionData(), $results) && !empty($results[1])) {
			return $this->_sanitizeAccount($results[1]);
		}

		// SEPA MT940 Structured
		if (preg_match('/^:61:.*\n(.*?)(\n|\:8)/im', $this->getCurrentTransactionData(), $results) && !empty($results[1])) {
			return $this->_sanitizeAccount($results[1]);
		}
		return '';
	}

	/**
	 * Overloaded: Rabo has different way of storing account name
	 * @inheritdoc
	 */
	protected function _parseTransactionAccountName() {
		$results = array();
		if (preg_match('/^:61:.*? (.*)/m', $this->getCurrentTransactionData(), $results) && !empty($results[1])) {
			$accountName = trim($results[1]);
			if (!empty($accountName)) {
				return $this->_sanitizeAccountName($accountName);
			}
		}

		// SEPA MT940 Structured
		if (preg_match('#/NAME/(.*?)/(REMI|ADDR)/#ms', $this->getCurrentTransactionData(), $results) && !empty($results[1])) {
			$accountName = trim($results[1]);
			if (!empty($accountName)) {
				return $this->_sanitizeAccountName($accountName);
			}
		}
		return '';
	}

	/**
	 * Overloaded: Rabo has different way of storing transaction value timestamps (ymd)
	 * @return int
	 */
	protected function _parseTransactionEntryTimestamp() {
		$results = array();
		if (preg_match('/^:60F:[C|D]([\d]{6})/m', $this->getCurrentStatementData(), $results) && !empty($results[1])) {
			return $this->_sanitizeTimestamp($results[1], 'ymd');
		}
		return 0;
	}

	/**
	 * Overloaded: Rabo has different way of storing transaction value timestamps (ymd)
	 * @return int
	 */
	protected function _parseTransactionValueTimestamp() {
		$results = array();
		if (preg_match('/^:61:([\d]{6})[C|D]/', $this->getCurrentTransactionData(), $results) && !empty($results[1])) {
			return $this->_sanitizeTimestamp($results[1], 'ymd');
		}
		return 0;
	}

	/**
	 * Overloaded: Rabo uses longer strings for accountnumbers
	 * @param string $string
	 * @return string
	 */
	protected function _sanitizeAccount($string) {
		$account = parent::_sanitizeAccount($string);
		if (strlen($account)>20 && strpos($account, '80000') == 0) {
			$account = substr($account, 5);
		}
		return $account;
	}

	/**
	 * Overloaded: Rabo encapsulates the description with /REMI/ for SEPA
	 * @param string $string
	 * @return string
	 */
	protected function _sanitizeDescription($string) {
		$description = parent::_sanitizeDescription($string);
		if (strpos($description, '/REMI/') !== false
				&& preg_match('#/REMI/(.*?)/(ISDT|CSID|RTRN)/#s', $description, $results) && !empty($results[1])) {
			return $results[1];
		}
		if (strpos($description, '/EREF/') !== false
				&& preg_match('#/EREF/(.*?)/(ORDP)/#s', $description, $results) && !empty($results[1])) {
			return $results[1];
		}
		return $description;
	}
}