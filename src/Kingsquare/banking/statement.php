<?php
namespace Kingsquare\Banking;

/**
 * @property array rawData used for debugging purposes
 *
 * @package Kingsquare\Banking
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Statement {
	private $_bank = '';
	private $_account = '';
	private $_transactions = array();
	private $_startPrice = 0;
	private $_endPrice = 0;
	private $_timestamp = 0;
	private $_number = '';

	/**
	 * @param string $var
	 */
	public function setBank($var) { $this->_bank = (string) $var; }

	/**
	 * @param string $var
	 */
	public function setAccount($var) { $this->_account = (string) $var; }

	/**
	 * @param Transaction[] $transactions
	 */
	public function setTransactions($transactions) { $this->_transactions = (array) $transactions; }

	/**
	 * @param int $var
	 */
	public function setStartPrice($var) { $this->_startPrice = (int) $var; }

	/**
	 * @param int $var
	 */
	public function setEndPrice($var) { $this->_endPrice = (int) $var; }

	/**
	 * @param int $var
	 */
	public function setTimestamp($var) { $this->_timestamp = (int) $var; }

	/**
	 * @param string $var
	 */
	public function setNumber($var) { $this->_number = (string) $var; }

	/**
	 * @return string
	 */
	public function getBank() { return $this->_bank; }

	/**
	 * @return string
	 */
	public function getAccount() { return $this->_account; }

	/**
	 * @return Transaction[]
	 */
	public function getTransactions() { return $this->_transactions; }

	/**
	 * @return int
	 */
	public function getStartPrice() { return $this->_startPrice; }

	/**
	 * @return int
	 */
	public function getEndPrice() { return $this->_endPrice; }

	/**
	 * @param string $format
	 * @return string
	 */
	public function getTimestamp($format = 'U') { return date($format, $this->_timestamp); }

	/**
	 * @return string
	 */
	public function getNumber() { return $this->_number; }

	/**
	 * @param Transaction $transaction
	 */
	public function addTransaction(Transaction $transaction) { $this->_transactions[] = $transaction; }

	/**
	 * @return int
	 */
	public function getDeltaPrice() { return ($this->getStartPrice() - $this->getEndPrice()); }
}