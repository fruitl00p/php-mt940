<?php

/**
 *
 * @package Kmt\Parser\Banking\Mt940
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 * @license http://opensource.org/licenses/gpl-2.0.php  Open Software License (GPLv2)
 */
class Engine_mt940_banking_parser {
	var $_rawData = '';
	var $_currentStatementData = '';
	var $_currentTransactionData = '';

	var $debug = false;

	/**
	 * reads the firstline of the string to guess which engine to use for parsing
	 * @param string $string
	 * @return Engine_mt940_banking_parser
	 */
	static function __getInstance($string) {
		$firstline = strtok($string, "\r\n\t");
		$bank = 'Unknown';
		if (strpos($firstline, 'ABNA') !== false) {
			$bank = 'Abn';
		} else if (strpos($firstline, 'INGB') !== false) {
			$bank = 'Ing';
		} else if (strpos($firstline, ':940:') !== false) {
			$bank = 'Rabo';
		}
		require_once __DIR__.'/engine/'.strtolower($bank).'.php';
		$class = $bank.'_engine_mt940_banking_parser';
		/* @var Engine_mt940_banking_parser $engine */
		$engine = new $class();
		if (is_a($engine, 'Engine_mt940_banking_parser')) {
			if (is_a($engine, 'Unknown_engine_mt940_banking_parser')) {
				trigger_error('Unknown mt940 parser loaded, thus reverted to default', E_USER_NOTICE);
			}
			$engine->loadString($string);
			return $engine;
		}
		return null;
	}

	/**
	 * loads the $string into _rawData
	 * this could be used to move it into handling of streams in the future
	 * @param string $string
	 * @return void
	 */
	function loadString($string) {
		$this->_rawData = trim($string);
	}

	/**
	 * actual parsing of the data
	 * @return Statement_Banking[]
	 */
	function parse() {
		$results = array();
		foreach ($this->_parseStatementData() as $this->_currentStatementData) {
			$statement = new Statement_Banking();
			if ($this->debug) {
				$statement->rawData = $this->_currentStatementData;
			}
			$statement->setBank($this->_parseStatementBank());
			$statement->setAccount($this->_parseStatementAccount());
			$statement->setStartPrice($this->_parseStatementStartPrice());
			$statement->setEndPrice($this->_parseStatementEndPrice());
			$statement->setTimestamp($this->_parseStatementTimestamp());
			$statement->setNumber($this->_parseStatementNumber());

			foreach ($this->_parseTransactionData() as $this->_currentTransactionData) {
				$transaction = new Transaction_Banking();
				if ($this->debug) {
					$transaction->rawData = $this->_currentTransactionData;
				}
				$transaction->setAccount($this->_parseTransactionAccount());
				$transaction->setAccountName($this->_parseTransactionAccountName());
				$transaction->setPrice($this->_parseTransactionPrice());
				$transaction->setDebitCredit($this->_parseTransactionDebitCredit());
				$transaction->setDescription($this->_parseTransactionDescription());
				$transaction->setValueTimestamp($this->_parseTransactionValueTimestamp());
				$transaction->setEntryTimestamp($this->_parseTransactionEntryTimestamp());
				$transaction->setTransactionCode($this->_parseTransactionCode());
				$statement->addTransaction($transaction);
			}
			$results[] = $statement;
		}
		return $results;
	}

	/**
	 * split the rawdata up into statementdata chunks
	 * @return array
	 */
	function _parseStatementData() {
		$results = preg_split('/(^:20:|^-X{,3}$|\Z)/sm',
				$this->getRawData(),
				-1,
				PREG_SPLIT_NO_EMPTY);
		array_shift($results); // remove the header
		return $results;
	}

	/**
	 * split the statment up into transaction chunks
	 * @return array
	 */
	function _parseTransactionData() {
		$results = array();
		preg_match_all('/^:61:(.*?)(?=^:61:|^-X{,3}$|\Z)/sm', $this->getCurrentStatementData(), $results);
		return ((!empty($results[0]))? $results[0] : array());
	}

	/**
	 * return the actual raw data string
	 * @return string _rawData
	 */
	function getRawData() {
		return $this->_rawData;
	}

	/**
	 * return the actual raw data string
	 * @return string _currentStatementData
	 */
	function getCurrentStatementData() {
		return $this->_currentStatementData;
	}

	/**
	 * return the actual raw data string
	 * @return string _currentTransactionData
	 */
	function getCurrentTransactionData() {
		return $this->_currentTransactionData;
	}

	// statement parsers, these work with currentStatementData

	/**
	 * return the actual raw data string
	 * @return string bank
	 */
	function _parseStatementBank() { return ''; }

	/**
	 * uses field 25 to gather accoutnumber
	 * @return string accountnumber
	 */
	function _parseStatementAccount() {
		$results = array();
		if (preg_match('/:25:([\d\.]+)*/', $this->getCurrentStatementData(), $results)
				&& !empty($results[1])) {
			return $this->_sanitizeAccount($results[1]);
		}
		return '';
	}

	/**
	 * uses field 60F to gather starting amount
	 * @return float price
	 */
	function _parseStatementStartPrice() {
		$results = array();
		if (preg_match('/:60F:.*EUR([\d,\.]+)*/', $this->getCurrentStatementData(), $results)
				&& !empty($results[1])) {
			return $this->_sanitizePrice($results[1]);
		}
		return '';
	}

	/**
	 * uses the 62F field to return end price of the statement
	 * @return float price
	 */
	function _parseStatementEndPrice() {
		$results = array();
		if (preg_match('/:62F:.*EUR([\d,\.]+)*/', $this->getCurrentStatementData(), $results)
				&& !empty($results[1])) {
			return $this->_sanitizePrice($results[1]);
		}
		return '';
	}

	/**
	 * uses the 60F field to determine the date of the statement
	 * @return int timestamp
	 */
	function _parseStatementTimestamp() {
		$results = array();
		if (preg_match('/:60F:[C|D](\d{6})*/', $this->getCurrentStatementData(), $results)
				&& !empty($results[1])) {
			return $this->_sanitizeTimestamp($results[1], 'ymd');
		}
		return 0;
	}

	/**
	 * uses the 28C field to determine the statement number
	 * @return string
	 */
	function _parseStatementNumber() {
		$results = array();
		if (preg_match('/:28C?:(.*)/', $this->getCurrentStatementData(), $results)
				&& !empty($results[1])) {
			return trim($results[1]);
		}
		return '';
	}

	// transaction parsers, these work with getCurrentTransactionData
	/**
	 * uses the 86 field to determine account number of the transaction
	 * @return string
	 */
	function _parseTransactionAccount() {
		$results = array();
		if (preg_match('/^:86:([\d\.]+)\s/im', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->_sanitizeAccount($results[1]);
		}
		return '';
	}

	/**
	 * uses the 86 field to determine accountname of the transaction
	 * @return string
	 */
	function _parseTransactionAccountName() {
		$results = array();
		if (preg_match('/[\n]:86:[\d\.]+ (.+)/', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->_sanitizeAccountName($results[1]);
		}
		return '';
	}

	/**
	 * uses the 61 field to determine amount/value of the transaction
	 * @return int
	 */
	function _parseTransactionPrice() {
		$results = array();
		if (preg_match('/^:61:.*[CD]([\d,\.]+)N/i', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->_sanitizePrice($results[1]);
		}
		return 0;
	}

	/**
	 * uses the 61 field to determine debit or credit of the transaction
	 * @return string
	 */
	function _parseTransactionDebitCredit() {
		$results = array();
		if (preg_match('/^:61:.*([CD])/i', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->_sanitizeDebitCredit($results[1]);
		}
		return '';
	}

	/**
	 * uses the 86 field to determine retrieve the full description of the transaction
	 * @return string
	 */
	function _parseTransactionDescription() {
		$results = array();
		if (preg_match_all('/[\n]:86:(.*?)(?=\n:|$)/s', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->_sanitizeDescription(implode(PHP_EOL, $results[1]));
		}
		return '';
	}

	/**
	 * uses the 61 field to determine the entry timestamp
	 * @return int
	 */
	function _parseTransactionEntryTimestamp() {
		$results = array();
		if (preg_match('/^:61:(\d{6})/', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->_sanitizeTimestamp($results[1], 'ymd');
		}
		return 0;
	}

	/**
	 * uses the 61 field to determine the value timestamp
	 * @return int
	 */
	function _parseTransactionValueTimestamp() {
		$results = array();
		if (preg_match('/^:61:(\d{6})/', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->_sanitizeTimestamp($results[1], 'ymd');
		}
		return 0;
	}

	/**
	 * uses the 61 field to get the bank specific transaction code
	 * @return string
	 */
	function _parseTransactionCode() {
		$results = array();
		if (preg_match('/^:61:.*?N(.{3}).*/', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return trim($results[1]);
		}
		return '';
	}

	/**
	 * @param string $string
	 * @return string
	 */
	function _sanitizeAccount($string) {
		static $crudeReplacements = array(
			'.' => '',
			' ' => '',
			'GIRO' => 'P',
		);
		$account = ltrim(
				str_replace(
					array_keys($crudeReplacements),
					array_values($crudeReplacements),
					strip_tags(trim($string))
				), '0');
		if (strlen($account)<9) $account = 'P'.$account;
		return $account;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	function _sanitizeAccountName($string) {
		return preg_replace('/[\s]+/', PHP_EOL, trim($string));
	}

	/**
	 * @param string $string
	 * @param string $inFormat
	 * @return int
	 */
	function _sanitizeTimestamp($string, $inFormat = 'ymd') {
		$date = DateTime::createFromFormat($inFormat, $string);
		$date->setTime(0, 0, 0);

		if ($date !== false) {
			return $date->format('U');
		}
		return 0;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	function _sanitizeDescription($string) {
		return preg_replace('/[\r\n]+/', PHP_EOL, trim($string));
	}

	/**
	 * @param string $string
	 * @return string
	 */
	function _sanitizeDebitCredit($string) {
		$debitOrCredit = substr(strtoupper($string), 0, 1);
		if ($debitOrCredit != Transaction_Banking::DEBIT && $debitOrCredit !=  Transaction_Banking::CREDIT) {
			trigger_error('wrong value for debit/credit ('.$string.')', E_USER_ERROR);
			$debitOrCredit = '';
		}
		return (string) $debitOrCredit;
	}

	/**
	 * @param string $string
	 * @return float
	 */
	function _sanitizePrice($string) {
		$floatPrice = ltrim(str_replace(',', '.', strip_tags(trim($string))), '0');
		return (float) $floatPrice;
	}
}