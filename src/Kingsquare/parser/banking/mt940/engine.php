<?php

namespace Kingsquare\Parser\Banking\Mt940;
use Kingsquare\Banking\Statement as Statement;
use Kingsquare\Banking\Transaction as Transaction;

/**
 * @package Kingsquare\Parser\Banking\Mt940
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
abstract class Engine {
	private $_rawData = '';
	protected $_currentStatementData = '';
	protected $_currentTransactionData = '';

	var $debug = false;

	/**
	 * reads the firstline of the string to guess which engine to use for parsing
	 * @param string $string
	 * @return Engine
	 */
	static function __getInstance($string) {
		$firstline = strtok($string, "\r\n\t");
		if (strpos($firstline, 'ABNA') !== false) {
			$engine = new Engine\Abn;
		} else if (strpos($firstline, 'INGB') !== false) {
			$engine = new Engine\Ing;
		} else if (strpos($firstline, ':940:') !== false) {
			$engine = new Engine\Rabo;
		} else {
			$engine = new Engine\Unknown;
			trigger_error('Unknown mt940 parser loaded, thus reverted to default', E_USER_NOTICE);
		}
		$engine->loadString($string);
		return $engine;
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
	 * @return Statement[]
	 */
	function parse() {
		$results = array();
		foreach ($this->parseStatementData() as $this->_currentStatementData) {
			$statement = new Statement();
			if ($this->debug) {
				$statement->rawData = $this->_currentStatementData;
			}
			$statement->setBank($this->parseStatementBank());
			$statement->setAccount($this->parseStatementAccount());
			$statement->setStartPrice($this->parseStatementStartPrice());
			$statement->setEndPrice($this->parseStatementEndPrice());
			$statement->setTimestamp($this->parseStatementTimestamp());
			$statement->setNumber($this->parseStatementNumber());

			foreach ($this->parseTransactionData() as $this->_currentTransactionData) {
				$transaction = new Transaction();
				if ($this->debug) {
					$transaction->rawData = $this->_currentTransactionData;
				}
				$transaction->setAccount($this->parseTransactionAccount());
				$transaction->setAccountName($this->parseTransactionAccountName());
				$transaction->setPrice($this->parseTransactionPrice());
				$transaction->setDebitCredit($this->parseTransactionDebitCredit());
				$transaction->setDescription($this->parseTransactionDescription());
				$transaction->setValueTimestamp($this->parseTransactionValueTimestamp());
				$transaction->setEntryTimestamp($this->parseTransactionEntryTimestamp());
				$transaction->setTransactionCode($this->parseTransactionCode());
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
	protected function parseStatementData() {
		$results = preg_split('/(^:20:|^-X{,3}$|\Z)/sm',
				$this->getRawData(),
				-1,
				PREG_SPLIT_NO_EMPTY);
		array_shift($results); // remove the header
		return $results;
	}

	/**
	 * split the statement up into transaction chunks
	 * @return array
	 */
	protected function parseTransactionData() {
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
	protected function parseStatementBank() { return ''; }

	/**
	 * uses field 25 to gather accoutnumber
	 * @return string accountnumber
	 */
	protected function parseStatementAccount() {
		$results = array();
		if (preg_match('/:25:([\d\.]+)*/', $this->getCurrentStatementData(), $results)
				&& !empty($results[1])) {
			return $this->sanitizeAccount($results[1]);
		}

		// SEPA / IBAN
		if (preg_match('/:25:[A-Z0-9]{8}([\d\.]+)*/', $this->getCurrentStatementData(), $results)
				&& !empty($results[1])) {
			return $this->sanitizeAccount($results[1]);
		}
		return '';
	}

	/**
	 * uses field 60F to gather starting amount
	 * @return float price
	 */
	protected function parseStatementStartPrice() {
		$results = array();
		if (preg_match('/:60F:.*EUR([\d,\.]+)*/', $this->getCurrentStatementData(), $results)
				&& !empty($results[1])) {
			return $this->sanitizePrice($results[1]);
		}
		return '';
	}

	/**
	 * uses the 62F field to return end price of the statement
	 * @return float price
	 */
	protected function parseStatementEndPrice() {
		$results = array();
		if (preg_match('/:62F:.*EUR([\d,\.]+)*/', $this->getCurrentStatementData(), $results)
				&& !empty($results[1])) {
			return $this->sanitizePrice($results[1]);
		}
		return '';
	}

	/**
	 * uses the 60F field to determine the date of the statement
	 * @return int timestamp
	 */
	protected function parseStatementTimestamp() {
		$results = array();
		if (preg_match('/:60F:[C|D](\d{6})*/', $this->getCurrentStatementData(), $results)
				&& !empty($results[1])) {
			return $this->sanitizeTimestamp($results[1], 'ymd');
		}
		return 0;
	}

	/**
	 * uses the 28C field to determine the statement number
	 * @return string
	 */
	protected function parseStatementNumber() {
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
	protected function parseTransactionAccount() {
		$results = array();
		if (preg_match('/^:86: ?([\d\.]+)\s/im', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->sanitizeAccount($results[1]);
		}
		return '';
	}

	/**
	 * uses the 86 field to determine accountname of the transaction
	 * @return string
	 */
	protected function parseTransactionAccountName() {
		$results = array();
		if (preg_match('/:86: ?[\d\.]+ (.+)/', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->sanitizeAccountName($results[1]);
		}
		return '';
	}

	/**
	 * uses the 61 field to determine amount/value of the transaction
	 * @return float
	 */
	protected function parseTransactionPrice() {
		$results = array();
		if (preg_match('/^:61:.*[CD]([\d,\.]+)N/i', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->sanitizePrice($results[1]);
		}
		return 0;
	}

	/**
	 * uses the 61 field to determine debit or credit of the transaction
	 * @return string
	 */
	protected function parseTransactionDebitCredit() {
		$results = array();
		if (preg_match('/^:61:\d+([CD])\d+/', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->sanitizeDebitCredit($results[1]);
		}
		return '';
	}

	/**
	 * uses the 86 field to determine retrieve the full description of the transaction
	 * @return string
	 */
	protected function parseTransactionDescription() {
		$results = array();
		if (preg_match_all('/[\n]:86:(.*?)(?=\n:|$)/s', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->sanitizeDescription(implode(PHP_EOL, $results[1]));
		}
		return '';
	}

	/**
	 * uses the 61 field to determine the entry timestamp
	 * @return int
	 */
	protected function parseTransactionEntryTimestamp() {
		$results = array();
		if (preg_match('/^:61:(\d{6})/', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->sanitizeTimestamp($results[1], 'ymd');
		}
		return 0;
	}

	/**
	 * uses the 61 field to determine the value timestamp
	 * @return int
	 */
	protected function parseTransactionValueTimestamp() {
		$results = array();
		if (preg_match('/^:61:(\d{6})/', $this->getCurrentTransactionData(), $results)
				&& !empty($results[1])) {
			return $this->sanitizeTimestamp($results[1], 'ymd');
		}
		return 0;
	}

	/**
	 * uses the 61 field to get the bank specific transaction code
	 * @return string
	 */
	protected function parseTransactionCode() {
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
	protected function sanitizeAccount($string) {
		static $crudeReplacements = array(
			'.' => '',
			' ' => '',
			'GIRO' => 'P',
		);
		// crude IBAN to 'old' converter
		if (preg_match('#[A-Z]{2}[0-9]{2}[A-Z]{4}(.*)#', $string, $results) && !empty($results[1])) {
			$string = $results[1];
		}

		$account = ltrim(
				str_replace(
					array_keys($crudeReplacements),
					array_values($crudeReplacements),
					strip_tags(trim($string))
				), '0');
		if ($account != '' && strlen($account)<9 && strpos($account, 'P') === false) {
			$account = 'P'.$account;
		}
		return $account;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	protected function sanitizeAccountName($string) {
		return preg_replace('/[\r\n]+/', '', trim($string));
	}

	/**
	 * @param string $string
	 * @param string $inFormat
	 * @return int
	 */
	protected function sanitizeTimestamp($string, $inFormat = 'ymd') {
		$date = \DateTime::createFromFormat($inFormat, $string);
		$date->setTime(0, 0, 0);
		if ($date !== false) {
			return (int) $date->format('U');
		}
		return 0;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	protected function sanitizeDescription($string) {
		return preg_replace('/[\r\n]+/', '', trim($string));
	}

	/**
	 * @param string $string
	 * @return string
	 */
	protected function sanitizeDebitCredit($string) {
		$debitOrCredit = strtoupper(substr((string) $string, 0, 1));
		if ($debitOrCredit != Transaction::DEBIT && $debitOrCredit != Transaction::CREDIT) {
			trigger_error('wrong value for debit/credit ('.$string.')', E_USER_ERROR);
			$debitOrCredit = '';
		}
		return $debitOrCredit;
	}

	/**
	 * @param string $string
	 * @return float
	 */
	protected function sanitizePrice($string) {
		$floatPrice = ltrim(str_replace(',', '.', strip_tags(trim($string))), '0');
		return (float) $floatPrice;
	}
}
